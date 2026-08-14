<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PdfToJpgController extends Controller
{
    public function index()
    {
        return view('pdf-to-jpg');
    }

    public function convert(Request $request)
    {
        $request->validate([
            'pdf' => 'required|file|mimes:pdf|max:20480',
        ]);

        $outputDirectory = null;
        $downloadPath = null;

        try {

            /*
            |--------------------------------------------------------------------------
            | Ghostscript Path
            |--------------------------------------------------------------------------
            */

            $ghostscript = 'C:\Program Files\gs\gs10.07.1\bin\gswin64c.exe';

            if (!file_exists($ghostscript)) {
                throw new \Exception(
                    'Ghostscript was not found at: ' . $ghostscript
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Uploaded PDF
            |--------------------------------------------------------------------------
            */

            $file = $request->file('pdf');

            $inputFile = $file->getRealPath();

            if (!$inputFile || !file_exists($inputFile)) {
                throw new \Exception(
                    'Uploaded PDF could not be found.'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Temporary Directory
            |--------------------------------------------------------------------------
            */

            $outputDirectory = storage_path(
                'app/pdf-to-jpg-' . uniqid()
            );

            if (!mkdir($outputDirectory, 0777, true)) {
                throw new \Exception(
                    'Could not create temporary directory.'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Output Pattern
            |--------------------------------------------------------------------------
            */

            $outputPattern =
                $outputDirectory .
                DIRECTORY_SEPARATOR .
                'page-%03d.jpg';


            /*
            |--------------------------------------------------------------------------
            | Ghostscript Command
            |--------------------------------------------------------------------------
            */

            $command =
                '"' . $ghostscript . '"' .
                ' -dSAFER' .
                ' -dBATCH' .
                ' -dNOPAUSE' .
                ' -sDEVICE=jpeg' .
                ' -r150' .
                ' -dJPEGQ=90' .
                ' -sOutputFile="' .
                $outputPattern .
                '"' .
                ' "' .
                $inputFile .
                '"';


            /*
            |--------------------------------------------------------------------------
            | Run Ghostscript
            |--------------------------------------------------------------------------
            */

            $output = [];
            $returnCode = 0;

            exec(
                $command . ' 2>&1',
                $output,
                $returnCode
            );


            /*
            |--------------------------------------------------------------------------
            | Check Ghostscript
            |--------------------------------------------------------------------------
            */

            if ($returnCode !== 0) {
                throw new \Exception(
                    "Ghostscript failed:\n\n" .
                    implode("\n", $output)
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Find JPG Files
            |--------------------------------------------------------------------------
            */

            $jpgFiles = glob(
                $outputDirectory .
                DIRECTORY_SEPARATOR .
                '*.jpg'
            );

            if (empty($jpgFiles)) {
                throw new \Exception(
                    'Ghostscript completed but no JPG images were created.'
                );
            }


            sort($jpgFiles);


            /*
            |--------------------------------------------------------------------------
            | ONE PAGE
            |--------------------------------------------------------------------------
            */

            if (count($jpgFiles) === 1) {

                $sourceFile = $jpgFiles[0];

                $downloadName = 'pdf-page-1.jpg';

                /*
                | Copy JPG to permanent storage
                */

                $downloadPath = storage_path(
                    'app' .
                    DIRECTORY_SEPARATOR .
                    $downloadName
                );

                if (!copy($sourceFile, $downloadPath)) {
                    throw new \Exception(
                        'Could not prepare JPG for download.'
                    );
                }

                if (
                    !file_exists($downloadPath) ||
                    filesize($downloadPath) <= 0
                ) {
                    throw new \Exception(
                        'Generated JPG is empty or missing.'
                    );
                }

                return response()
                    ->download(
                        $downloadPath,
                        $downloadName
                    )
                    ->deleteFileAfterSend(true);
            }


            /*
            |--------------------------------------------------------------------------
            | MULTIPLE PAGES
            |--------------------------------------------------------------------------
            | Create ZIP in permanent storage
            |--------------------------------------------------------------------------
            */

            $zipName =
                'pdf-to-jpg-' .
                date('Ymd-His') .
                '.zip';

            $zipPath = storage_path(
                'app' .
                DIRECTORY_SEPARATOR .
                $zipName
            );

            $downloadPath = $zipPath;


            /*
            |--------------------------------------------------------------------------
            | Create ZIP
            |--------------------------------------------------------------------------
            */

            $zip = new \ZipArchive();

            $result = $zip->open(
                $zipPath,
                \ZipArchive::CREATE |
                \ZipArchive::OVERWRITE
            );

            if ($result !== true) {
                throw new \Exception(
                    'Could not create ZIP file.'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Add JPG Files
            |--------------------------------------------------------------------------
            */

            foreach ($jpgFiles as $index => $jpgFile) {

                if (
                    !file_exists($jpgFile) ||
                    !is_readable($jpgFile)
                ) {
                    $zip->close();

                    throw new \Exception(
                        'Generated JPG file could not be read.'
                    );
                }

                $pageNumber = $index + 1;

                $zip->addFile(
                    $jpgFile,
                    'page-' .
                    str_pad(
                        $pageNumber,
                        3,
                        '0',
                        STR_PAD_LEFT
                    ) .
                    '.jpg'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Close ZIP
            |--------------------------------------------------------------------------
            */

            $zip->close();


            /*
            |--------------------------------------------------------------------------
            | Check ZIP
            |--------------------------------------------------------------------------
            */

            if (
                !file_exists($zipPath) ||
                filesize($zipPath) <= 0
            ) {
                throw new \Exception(
                    'ZIP file could not be created.'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Download ZIP
            |--------------------------------------------------------------------------
            */

            return response()
                ->download(
                    $zipPath,
                    $zipName
                )
                ->deleteFileAfterSend(true);


        } catch (\Throwable $e) {

            /*
            |--------------------------------------------------------------------------
            | Remove Permanent Download File If Error
            |--------------------------------------------------------------------------
            */

            if (
                $downloadPath &&
                file_exists($downloadPath)
            ) {
                @unlink($downloadPath);
            }


            return back()->withErrors([
                'pdf' =>
                    'PDF to JPG conversion failed: ' .
                    $e->getMessage()
            ]);

        } finally {

            /*
            |--------------------------------------------------------------------------
            | Cleanup Temporary Directory
            |--------------------------------------------------------------------------
            |
            | IMPORTANT:
            | This only removes the temporary Ghostscript folder.
            | The actual download file is kept outside this folder
            | until Laravel finishes sending it.
            |--------------------------------------------------------------------------
            */

            if (
                $outputDirectory &&
                is_dir($outputDirectory)
            ) {

                $files = glob(
                    $outputDirectory .
                    DIRECTORY_SEPARATOR .
                    '*'
                );

                foreach ($files as $tempFile) {

                    if (is_file($tempFile)) {
                        @unlink($tempFile);
                    }
                }

                @rmdir($outputDirectory);
            }
        }
    }
}