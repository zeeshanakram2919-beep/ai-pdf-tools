<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CompressPdfController extends Controller
{
    public function index()
    {
        return view('compress-pdf');
    }

    public function compress(Request $request)
    {
        $request->validate([
            'pdf' => 'required|file|mimes:pdf|max:20480',
        ]);

        try {
            $file = $request->file('pdf');

            $inputPath = $file->getRealPath();

            if (!$inputPath || !file_exists($inputPath)) {
                return back()->withErrors([
                    'pdf' => 'Uploaded PDF file could not be found.'
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Detect Ghostscript
            |--------------------------------------------------------------------------
            */

            if (PHP_OS_FAMILY === 'Windows') {
                $ghostscript = 'C:\Program Files\gs\gs10.07.1\bin\gswin64c.exe';
            } else {
                $ghostscript = '/usr/bin/gs';
            }

            /*
            |--------------------------------------------------------------------------
            | Check Ghostscript
            |--------------------------------------------------------------------------
            */

            if (!file_exists($ghostscript)) {
                return back()->withErrors([
                    'pdf' => 'Ghostscript is not installed on the server.'
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Output directory
            |--------------------------------------------------------------------------
            */

            $storagePath = storage_path('app');

            if (!is_dir($storagePath)) {
                mkdir($storagePath, 0777, true);
            }

            $fileName = 'compressed-' . time() . '.pdf';

            $outputPath = $storagePath . DIRECTORY_SEPARATOR . $fileName;

            /*
            |--------------------------------------------------------------------------
            | Ghostscript Compression
            |--------------------------------------------------------------------------
            */

            $command =
                escapeshellarg($ghostscript)
                . ' -sDEVICE=pdfwrite'
                . ' -dCompatibilityLevel=1.4'
                . ' -dPDFSETTINGS=/ebook'
                . ' -dNOPAUSE'
                . ' -dQUIET'
                . ' -dBATCH'
                . ' -sOutputFile=' . escapeshellarg($outputPath)
                . ' ' . escapeshellarg($inputPath)
                . ' 2>&1';

            $output = [];
            $returnCode = 0;

            exec($command, $output, $returnCode);

            /*
            |--------------------------------------------------------------------------
            | Check Compression Result
            |--------------------------------------------------------------------------
            */

            if (
                $returnCode !== 0 ||
                !file_exists($outputPath) ||
                filesize($outputPath) === 0
            ) {
                $errorMessage = !empty($output)
                    ? implode("\n", $output)
                    : 'Ghostscript could not compress the PDF.';

                return back()->withErrors([
                    'pdf' => 'PDF compression failed: ' . $errorMessage
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Download
            |--------------------------------------------------------------------------
            */

            return response()
                ->download($outputPath, $fileName)
                ->deleteFileAfterSend(true);

        } catch (\Throwable $e) {

            return back()->withErrors([
                'pdf' => 'PDF compression failed: ' . $e->getMessage()
            ]);
        }
    }
}