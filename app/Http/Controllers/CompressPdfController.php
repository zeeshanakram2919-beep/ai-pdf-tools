<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class CompressPdfController extends Controller
{
    /**
     * Show compression page
     */
    public function index()
    {
        return view('compress-pdf');
    }

    /**
     * Compress PDF
     */
    public function compress(Request $request)
    {
        // Validate uploaded PDF
        $request->validate([
            'pdf' => [
                'required',
                'file',
                'mimes:pdf',
                'max:51200', // 50 MB
            ],
            'quality' => [
                'nullable',
                'in:screen,ebook,printer,prepress',
            ],
        ]);

        $uploadedFile = $request->file('pdf');

        /*
        |--------------------------------------------------------------------------
        | Compression quality
        |--------------------------------------------------------------------------
        |
        | screen   = maximum compression
        | ebook    = recommended compression
        | printer  = better quality
        | prepress = highest quality
        |
        */

        $quality = $request->input('quality', 'ebook');

        /*
        |--------------------------------------------------------------------------
        | Temporary directories
        |--------------------------------------------------------------------------
        */

        $tempDirectory = storage_path('app/compression');

        if (!File::exists($tempDirectory)) {
            File::makeDirectory(
                $tempDirectory,
                0755,
                true
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Generate unique filenames
        |--------------------------------------------------------------------------
        */

        $uniqueId = Str::uuid()->toString();

        $originalPath = $tempDirectory . DIRECTORY_SEPARATOR
            . $uniqueId . '_original.pdf';

        $compressedPath = $tempDirectory . DIRECTORY_SEPARATOR
            . $uniqueId . '_compressed.pdf';

        /*
        |--------------------------------------------------------------------------
        | Store uploaded PDF
        |--------------------------------------------------------------------------
        */

        $uploadedFile->move(
            $tempDirectory,
            basename($originalPath)
        );

        try {

            /*
            |--------------------------------------------------------------------------
            | Find Ghostscript executable
            |--------------------------------------------------------------------------
            */

            $ghostscript = $this->findGhostscript();

            if (!$ghostscript) {
                throw new \RuntimeException(
                    'Ghostscript is not installed or could not be found on the server.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Ghostscript command
            |--------------------------------------------------------------------------
            |
            | We use PDFSETTINGS plus explicit image downsampling/compression.
            | This gives Ghostscript more control over image-heavy PDFs.
            |
            */

            $command = [
                $ghostscript,

                '-sDEVICE=pdfwrite',

                '-dCompatibilityLevel=1.4',

                '-dNOPAUSE',
                '-dQUIET',
                '-dBATCH',

                '-dSAFER',

                /*
                |--------------------------------------------------------------------------
                | Compression profile
                |--------------------------------------------------------------------------
                */

                '-dPDFSETTINGS=/' . $quality,

                /*
                |--------------------------------------------------------------------------
                | Color image compression
                |--------------------------------------------------------------------------
                */

                '-dDownsampleColorImages=true',
                '-dColorImageDownsampleType=/Bicubic',
                '-dColorImageResolution=120',

                /*
                |--------------------------------------------------------------------------
                | Gray image compression
                |--------------------------------------------------------------------------
                */

                '-dDownsampleGrayImages=true',
                '-dGrayImageDownsampleType=/Bicubic',
                '-dGrayImageResolution=120',

                /*
                |--------------------------------------------------------------------------
                | Monochrome image compression
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
            | Run Ghostscript
            |--------------------------------------------------------------------------
            */

            $process = new Process($command);

            $process->setTimeout(120);

            $process->run();

            /*
            |--------------------------------------------------------------------------
            | Check Ghostscript result
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
                    'Ghostscript compression failed.'
                    . ($errorMessage !== ''
                        ? ' ' . $errorMessage
                        : '')
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Check compressed file
            |--------------------------------------------------------------------------
            */

            if (
                !File::exists($compressedPath) ||
                File::size($compressedPath) <= 0
            ) {
                throw new \RuntimeException(
                    'Ghostscript did not create a valid compressed PDF.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Compare file sizes
            |--------------------------------------------------------------------------
            */

            $originalSize = File::size($originalPath);
            $compressedSize = File::size($compressedPath);

            /*
            |--------------------------------------------------------------------------
            | If compression did not reduce the file
            |--------------------------------------------------------------------------
            |
            | Very important:
            |
            | Sometimes a PDF is already optimized.
            | Ghostscript can then create a file that is the same size
            | or even larger.
            |
            | In that situation we return the original PDF instead.
            |
            */

            if ($compressedSize >= $originalSize) {

                $downloadName = pathinfo(
                    $uploadedFile->getClientOriginalName(),
                    PATHINFO_FILENAME
                ) . '-compressed.pdf';

                /*
                |--------------------------------------------------------------------------
                | Clean compressed temporary file
                |--------------------------------------------------------------------------
                */

                if (File::exists($compressedPath)) {
                    File::delete($compressedPath);
                }

                return response()->download(
                    $originalPath,
                    $downloadName,
                    [
                        'Content-Type' => 'application/pdf',
                    ]
                )->deleteFileAfterSend(true);
            }

            /*
            |--------------------------------------------------------------------------
            | Calculate compression percentage
            |--------------------------------------------------------------------------
            */

            $savedBytes = $originalSize - $compressedSize;

            $compressionPercentage = $originalSize > 0
                ? round(
                    ($savedBytes / $originalSize) * 100,
                    2
                )
                : 0;

            /*
            |--------------------------------------------------------------------------
            | Download compressed PDF
            |--------------------------------------------------------------------------
            */

            $downloadName = pathinfo(
                $uploadedFile->getClientOriginalName(),
                PATHINFO_FILENAME
            ) . '-compressed.pdf';

            /*
            |--------------------------------------------------------------------------
            | Delete original after response
            |--------------------------------------------------------------------------
            |
            | We don't need to keep the uploaded PDF after processing.
            |
            */

            if (File::exists($originalPath)) {
                File::delete($originalPath);
            }

            /*
            |--------------------------------------------------------------------------
            | Return compressed PDF
            |--------------------------------------------------------------------------
            */

            return response()->download(
                $compressedPath,
                $downloadName,
                [
                    'Content-Type' => 'application/pdf',
                    'X-Original-Size' => $originalSize,
                    'X-Compressed-Size' => $compressedSize,
                    'X-Compression-Percent' => $compressionPercentage,
                ]
            )->deleteFileAfterSend(true);

        } catch (\Throwable $e) {

            /*
            |--------------------------------------------------------------------------
            | Cleanup files if something goes wrong
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
            | Return user-friendly error
            |--------------------------------------------------------------------------
            */

            return back()
                ->withInput()
                ->with(
                    'error',
                    'PDF compression failed: ' . $e->getMessage()
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
        | Railway / Linux
        |--------------------------------------------------------------------------
        */

        $linuxPaths = [
            '/usr/bin/gs',
            '/usr/local/bin/gs',
            '/bin/gs',
        ];

        foreach ($linuxPaths as $path) {
            if (is_file($path) && is_executable($path)) {
                return $path;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Windows
        |--------------------------------------------------------------------------
        */

        $windowsPaths = [
            'C:\\Program Files\\gs\\bin\\gswin64c.exe',
            'C:\\Program Files\\gs\\bin\\gswin32c.exe',
        ];

        foreach ($windowsPaths as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Try PATH
        |--------------------------------------------------------------------------
        */

        if (PHP_OS_FAMILY === 'Windows') {

            $process = new Process([
                'where',
                'gswin64c',
            ]);

            $process->run();

            if ($process->isSuccessful()) {
                $path = trim(
                    $process->getOutput()
                );

                if ($path !== '') {
                    return strtok($path, PHP_EOL);
                }
            }

            $process = new Process([
                'where',
                'gs',
            ]);

            $process->run();

            if ($process->isSuccessful()) {
                $path = trim(
                    $process->getOutput()
                );

                if ($path !== '') {
                    return strtok($path, PHP_EOL);
                }
            }

        } else {

            $process = new Process([
                'which',
                'gs',
            ]);

            $process->run();

            if ($process->isSuccessful()) {
                $path = trim(
                    $process->getOutput()
                );

                if ($path !== '') {
                    return $path;
                }
            }
        }

        return null;
    }
}