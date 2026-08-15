<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class CompressPdfController extends Controller
{
    /**
     * Show compression page.
     */
    public function index()
    {
        return view('compress-pdf');
    }

    /**
     * Compress uploaded PDF.
     */
    public function compress(Request $request)
    {
        $request->validate([
            'pdf' => [
                'required',
                'file',
                'mimes:pdf',
                'max:51200',
            ],
            'quality' => [
                'nullable',
                'in:screen,ebook,printer,prepress',
            ],
        ]);

        $uploadedFile = $request->file('pdf');

        $quality = $request->input('quality', 'ebook');

        /*
        |--------------------------------------------------------------------------
        | Create dedicated directories
        |--------------------------------------------------------------------------
        */

        $compressionDirectory = storage_path(
            'app' . DIRECTORY_SEPARATOR . 'compression'
        );

        $ghostscriptTempDirectory = storage_path(
            'app' . DIRECTORY_SEPARATOR . 'gs-temp'
        );

        $this->ensureDirectory($compressionDirectory);
        $this->ensureDirectory($ghostscriptTempDirectory);

        /*
        |--------------------------------------------------------------------------
        | Make sure directories are writable
        |--------------------------------------------------------------------------
        */

        if (!is_writable($compressionDirectory)) {
            throw new \RuntimeException(
                'Compression directory is not writable: ' .
                $compressionDirectory
            );
        }

        if (!is_writable($ghostscriptTempDirectory)) {
            throw new \RuntimeException(
                'Ghostscript temporary directory is not writable: ' .
                $ghostscriptTempDirectory
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Unique files
        |--------------------------------------------------------------------------
        */

        $uniqueId = Str::uuid()->toString();

        $originalPath = $compressionDirectory .
            DIRECTORY_SEPARATOR .
            $uniqueId .
            '_original.pdf';

        $compressedPath = $compressionDirectory .
            DIRECTORY_SEPARATOR .
            $uniqueId .
            '_compressed.pdf';

        try {

            /*
            |--------------------------------------------------------------------------
            | Move uploaded PDF
            |--------------------------------------------------------------------------
            */

            $uploadedFile->move(
                $compressionDirectory,
                basename($originalPath)
            );

            if (!File::exists($originalPath)) {
                throw new \RuntimeException(
                    'Uploaded PDF could not be stored.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Find Ghostscript
            |--------------------------------------------------------------------------
            */

            $ghostscript = $this->findGhostscript();

            if (!$ghostscript) {
                throw new \RuntimeException(
                    'Ghostscript could not be found.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Ghostscript command
            |--------------------------------------------------------------------------
            */

            $command = [
                $ghostscript,

                '-sDEVICE=pdfwrite',
                '-dCompatibilityLevel=1.4',

                '-dNOPAUSE',
                '-dBATCH',
                '-dSAFER',

                '-dPDFSETTINGS=/' . $quality,

                /*
                |--------------------------------------------------------------------------
                | Color images
                |--------------------------------------------------------------------------
                */

                '-dDownsampleColorImages=true',
                '-dColorImageDownsampleType=/Bicubic',
                '-dColorImageResolution=120',

                /*
                |--------------------------------------------------------------------------
                | Grayscale images
                |--------------------------------------------------------------------------
                */

                '-dDownsampleGrayImages=true',
                '-dGrayImageDownsampleType=/Bicubic',
                '-dGrayImageResolution=120',

                /*
                |--------------------------------------------------------------------------
                | Monochrome images
                |--------------------------------------------------------------------------
                */

                '-dDownsampleMonoImages=true',
                '-dMonoImageDownsampleType=/Subsample',
                '-dMonoImageResolution=150',

                /*
                |--------------------------------------------------------------------------
                | JPEG compression
                |--------------------------------------------------------------------------
                */

                '-dAutoFilterColorImages=false',
                '-dColorImageFilter=/DCTEncode',

                '-dAutoFilterGrayImages=false',
                '-dGrayImageFilter=/DCTEncode',

                /*
                |--------------------------------------------------------------------------
                | Output
                |--------------------------------------------------------------------------
                */

                '-sOutputFile=' . $compressedPath,

                /*
                |--------------------------------------------------------------------------
                | Input
                |--------------------------------------------------------------------------
                */

                $originalPath,
            ];

            /*
            |--------------------------------------------------------------------------
            | Run Ghostscript with dedicated TEMP/TMP
            |--------------------------------------------------------------------------
            */

            $process = new Process($command);

            $process->setTimeout(120);

            /*
            | This is the important fix.
            |
            | Ghostscript will use our writable directory instead
            | of an empty/broken Windows temporary path.
            */

            $process->setEnv([
                'TEMP' => $ghostscriptTempDirectory,
                'TMP' => $ghostscriptTempDirectory,
                'TMPDIR' => $ghostscriptTempDirectory,
            ]);

            $process->run();

            /*
            |--------------------------------------------------------------------------
            | Check process
            |--------------------------------------------------------------------------
            */

            if (!$process->isSuccessful()) {

                $errorOutput = trim(
                    $process->getErrorOutput()
                );

                $standardOutput = trim(
                    $process->getOutput()
                );

                $errorMessage = $errorOutput !== ''
                    ? $errorOutput
                    : $standardOutput;

                throw new \RuntimeException(
                    'Ghostscript compression failed.' .
                    ($errorMessage !== ''
                        ? ' ' . $errorMessage
                        : '')
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Verify output
            |--------------------------------------------------------------------------
            */

            if (
                !File::exists($compressedPath) ||
                File::size($compressedPath) <= 0
            ) {
                throw new \RuntimeException(
                    'Ghostscript completed but did not create a valid PDF.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Compare sizes
            |--------------------------------------------------------------------------
            */

            $originalSize = File::size(
                $originalPath
            );

            $compressedSize = File::size(
                $compressedPath
            );

            /*
            |--------------------------------------------------------------------------
            | Download filename
            |--------------------------------------------------------------------------
            */

            $downloadName =
                pathinfo(
                    $uploadedFile->getClientOriginalName(),
                    PATHINFO_FILENAME
                ) .
                '-compressed.pdf';

            /*
            |--------------------------------------------------------------------------
            | If compression did not reduce file size
            |--------------------------------------------------------------------------
            |
            | Return original instead of returning a bigger PDF.
            */

            if ($compressedSize >= $originalSize) {

                File::delete($compressedPath);

                return response()
                    ->download(
                        $originalPath,
                        $downloadName,
                        [
                            'Content-Type' =>
                                'application/pdf',
                        ]
                    )
                    ->deleteFileAfterSend(true);
            }

            /*
            |--------------------------------------------------------------------------
            | Compression percentage
            |--------------------------------------------------------------------------
            */

            $compressionPercentage = round(
                (
                    ($originalSize - $compressedSize)
                    / $originalSize
                ) * 100,
                2
            );

            /*
            |--------------------------------------------------------------------------
            | Remove original before returning compressed file
            |--------------------------------------------------------------------------
            */

            File::delete($originalPath);

            /*
            |--------------------------------------------------------------------------
            | Download compressed PDF
            |--------------------------------------------------------------------------
            */

            return response()
                ->download(
                    $compressedPath,
                    $downloadName,
                    [
                        'Content-Type' =>
                            'application/pdf',

                        'X-Original-Size' =>
                            $originalSize,

                        'X-Compressed-Size' =>
                            $compressedSize,

                        'X-Compression-Percent' =>
                            $compressionPercentage,
                    ]
                )
                ->deleteFileAfterSend(true);

        } catch (\Throwable $e) {

            /*
            |--------------------------------------------------------------------------
            | Cleanup
            |--------------------------------------------------------------------------
            */

            if (File::exists($originalPath)) {
                File::delete($originalPath);
            }

            if (File::exists($compressedPath)) {
                File::delete($compressedPath);
            }

            /*
            |--------------------------------------------------------------------------
            | Return error to page
            |--------------------------------------------------------------------------
            */

            return back()
                ->withInput()
                ->with(
                    'error',
                    'PDF compression failed: ' .
                    $e->getMessage()
                );
        }
    }

    /**
     * Create directory if it does not exist.
     */
    private function ensureDirectory(
        string $directory
    ): void {
        if (!File::exists($directory)) {
            File::makeDirectory(
                $directory,
                0755,
                true
            );
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

                    $firstPath = trim(
                        strtok(
                            $output,
                            PHP_EOL
                        )
                    );

                    if (
                        $firstPath !== '' &&
                        is_file($firstPath)
                    ) {
                        return $firstPath;
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