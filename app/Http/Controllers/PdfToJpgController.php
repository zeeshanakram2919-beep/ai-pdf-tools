<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class PdfToJpgController extends Controller
{
    /**
     * Show PDF to JPG page.
     */
    public function index()
    {
        return view('pdf-to-jpg');
    }

    /**
     * Convert PDF pages to JPG images.
     */
    public function convert(Request $request)
    {
        $request->validate([
            'pdf' => [
                'required',
                'file',
                'mimes:pdf',
                'max:20480',
            ],
        ]);

        $outputDirectory = null;
        $downloadPath = null;

        try {

            /*
            |--------------------------------------------------------------------------
            | Find Ghostscript
            |--------------------------------------------------------------------------
            */

            $ghostscript = $this->findGhostscript();

            if (!$ghostscript) {
                throw new \RuntimeException(
                    'Ghostscript could not be found on this server.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Uploaded PDF
            |--------------------------------------------------------------------------
            */

            $file = $request->file('pdf');

            $inputFile = $file->getRealPath();

            if (
                !$inputFile ||
                !file_exists($inputFile)
            ) {
                throw new \RuntimeException(
                    'Uploaded PDF could not be found.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Create unique temporary directories
            |--------------------------------------------------------------------------
            */

            $outputDirectory = storage_path(
                'app' .
                DIRECTORY_SEPARATOR .
                'pdf-to-jpg-' .
                Str::uuid()->toString()
            );

            $ghostscriptTempDirectory = storage_path(
                'app' .
                DIRECTORY_SEPARATOR .
                'gs-temp-' .
                Str::uuid()->toString()
            );

            File::makeDirectory(
                $outputDirectory,
                0755,
                true
            );

            File::makeDirectory(
                $ghostscriptTempDirectory,
                0755,
                true
            );

            /*
            |--------------------------------------------------------------------------
            | Verify directories
            |--------------------------------------------------------------------------
            */

            if (!is_writable($outputDirectory)) {
                throw new \RuntimeException(
                    'PDF-to-JPG temporary directory is not writable.'
                );
            }

            if (!is_writable($ghostscriptTempDirectory)) {
                throw new \RuntimeException(
                    'Ghostscript temporary directory is not writable.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Output pattern
            |--------------------------------------------------------------------------
            */

            $outputPattern =
                $outputDirectory .
                DIRECTORY_SEPARATOR .
                'page-%03d.jpg';

            /*
            |--------------------------------------------------------------------------
            | Ghostscript command
            |--------------------------------------------------------------------------
            */

            $command = [
                $ghostscript,

                '-dSAFER',
                '-dBATCH',
                '-dNOPAUSE',

                '-sDEVICE=jpeg',

                /*
                |--------------------------------------------------------------------------
                | 150 DPI
                |--------------------------------------------------------------------------
                */

                '-r150',

                /*
                |--------------------------------------------------------------------------
                | JPEG Quality
                |--------------------------------------------------------------------------
                */

                '-dJPEGQ=90',

                /*
                |--------------------------------------------------------------------------
                | Output
                |--------------------------------------------------------------------------
                */

                '-sOutputFile=' . $outputPattern,

                /*
                |--------------------------------------------------------------------------
                | Input
                |--------------------------------------------------------------------------
                */

                $inputFile,
            ];

            /*
            |--------------------------------------------------------------------------
            | Run Ghostscript
            |--------------------------------------------------------------------------
            */

            $process = new Process($command);

            $process->setTimeout(120);

            /*
            |--------------------------------------------------------------------------
            | Dedicated temporary directory
            |--------------------------------------------------------------------------
            */

            $process->setEnv([
                'TEMP' => $ghostscriptTempDirectory,
                'TMP' => $ghostscriptTempDirectory,
                'TMPDIR' => $ghostscriptTempDirectory,
            ]);

            $process->run();

            /*
            |--------------------------------------------------------------------------
            | Check Ghostscript
            |--------------------------------------------------------------------------
            */

            if (!$process->isSuccessful()) {

                $errorOutput = trim(
                    $process->getErrorOutput()
                );

                $standardOutput = trim(
                    $process->getOutput()
                );

                $errorMessage =
                    $errorOutput !== ''
                    ? $errorOutput
                    : $standardOutput;

                throw new \RuntimeException(
                    'Ghostscript failed.' .
                    (
                        $errorMessage !== ''
                        ? ' ' . $errorMessage
                        : ''
                    )
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Find generated JPGs
            |--------------------------------------------------------------------------
            */

            $jpgFiles = glob(
                $outputDirectory .
                DIRECTORY_SEPARATOR .
                '*.jpg'
            );

            if (empty($jpgFiles)) {
                throw new \RuntimeException(
                    'Ghostscript completed but no JPG images were created.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Sort pages correctly
            |--------------------------------------------------------------------------
            */

            natsort($jpgFiles);

            $jpgFiles = array_values($jpgFiles);

            /*
            |--------------------------------------------------------------------------
            | One page
            |--------------------------------------------------------------------------
            */

            if (count($jpgFiles) === 1) {

                $sourceFile = $jpgFiles[0];

                $downloadName =
                    'pdf-page-1.jpg';

                $downloadPath =
                    storage_path(
                        'app' .
                        DIRECTORY_SEPARATOR .
                        $downloadName
                    );

                if (!copy(
                    $sourceFile,
                    $downloadPath
                )) {
                    throw new \RuntimeException(
                        'Could not prepare JPG for download.'
                    );
                }

                if (
                    !file_exists($downloadPath) ||
                    filesize($downloadPath) <= 0
                ) {
                    throw new \RuntimeException(
                        'Generated JPG is empty or missing.'
                    );
                }

                return response()
                    ->download(
                        $downloadPath,
                        $downloadName,
                        [
                            'Content-Type' =>
                                'image/jpeg',
                        ]
                    )
                    ->deleteFileAfterSend(true);
            }

            /*
            |--------------------------------------------------------------------------
            | Multiple pages → ZIP
            |--------------------------------------------------------------------------
            */

            $zipName =
                'pdf-to-jpg-' .
                date('Ymd-His') .
                '-' .
                uniqid() .
                '.zip';

            $zipPath =
                storage_path(
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
                throw new \RuntimeException(
                    'Could not create ZIP file.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Add JPG files
            |--------------------------------------------------------------------------
            */

            foreach (
                $jpgFiles as $index => $jpgFile
            ) {

                if (
                    !file_exists($jpgFile) ||
                    !is_readable($jpgFile)
                ) {
                    $zip->close();

                    throw new \RuntimeException(
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
            | Verify ZIP
            |--------------------------------------------------------------------------
            */

            if (
                !file_exists($zipPath) ||
                filesize($zipPath) <= 0
            ) {
                throw new \RuntimeException(
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
                    $zipName,
                    [
                        'Content-Type' =>
                            'application/zip',
                    ]
                )
                ->deleteFileAfterSend(true);

        } catch (\Throwable $e) {

            /*
            |--------------------------------------------------------------------------
            | Remove failed download
            |--------------------------------------------------------------------------
            */

            if (
                $downloadPath &&
                file_exists($downloadPath)
            ) {
                @unlink($downloadPath);
            }

            /*
            |--------------------------------------------------------------------------
            | Return error
            |--------------------------------------------------------------------------
            */

            return back()
                ->withInput()
                ->withErrors([
                    'pdf' =>
                        'PDF to JPG conversion failed: ' .
                        $e->getMessage(),
                ]);

        } finally {

            /*
            |--------------------------------------------------------------------------
            | Cleanup generated JPG directory
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

    /**
     * Find Ghostscript executable.
     */
    private function findGhostscript(): ?string
    {
        /*
        |--------------------------------------------------------------------------
        | Windows
        |--------------------------------------------------------------------------
        */

        if (PHP_OS_FAMILY === 'Windows') {

            $windowsPaths = [
                'C:\\Program Files\\gs\\gs10.07.1\\bin\\gswin64c.exe',
                'C:\\Program Files\\gs\\bin\\gswin64c.exe',
                'C:\\Program Files\\gs\\gs10.07.1\\bin\\gswin32c.exe',
                'C:\\Program Files\\gs\\bin\\gswin32c.exe',
            ];

            foreach ($windowsPaths as $path) {

                if (is_file($path)) {
                    return $path;
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Windows PATH
            |--------------------------------------------------------------------------
            */

            $process = new Process([
                'where.exe',
                'gswin64c.exe',
            ]);

            $process->setTimeout(10);

            $process->run();

            if ($process->isSuccessful()) {

                $output = trim(
                    $process->getOutput()
                );

                if ($output !== '') {

                    $path = trim(
                        strtok(
                            $output,
                            PHP_EOL
                        )
                    );

                    if (
                        $path !== '' &&
                        is_file($path)
                    ) {
                        return $path;
                    }
                }
            }

            return null;
        }

        /*
        |--------------------------------------------------------------------------
        | Linux / Railway
        |--------------------------------------------------------------------------
        */

        $linuxPaths = [
            '/usr/bin/gs',
            '/usr/local/bin/gs',
            '/bin/gs',
        ];

        foreach ($linuxPaths as $path) {

            if (
                is_file($path) &&
                is_executable($path)
            ) {
                return $path;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Linux PATH
        |--------------------------------------------------------------------------
        */

        $process = new Process([
            'which',
            'gs',
        ]);

        $process->setTimeout(10);

        $process->run();

        if ($process->isSuccessful()) {

            $path = trim(
                $process->getOutput()
            );

            if (
                $path !== '' &&
                is_file($path)
            ) {
                return $path;
            }
        }

        return null;
    }
}