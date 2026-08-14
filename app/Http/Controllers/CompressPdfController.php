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

            // Uploaded PDF
            $file = $request->file('pdf');

            // Input PDF path
            $inputPath = $file->getRealPath();

            // Create unique filename
            $fileName = 'compressed-' . time() . '.pdf';

            // Storage directory
            $storagePath = storage_path('app');

            // Make sure storage directory exists
            if (!is_dir($storagePath)) {
                mkdir($storagePath, 0777, true);
            }

            // Output PDF path
            $outputPath = $storagePath . DIRECTORY_SEPARATOR . $fileName;

            /*
            |--------------------------------------------------------------------------
            | Ghostscript
            |--------------------------------------------------------------------------
            */

            $ghostscript = 'C:\Program Files\gs\gs10.07.1\bin\gswin64c.exe';

            /*
            |--------------------------------------------------------------------------
            | Compression command
            |--------------------------------------------------------------------------
            */

            $command = '"' . $ghostscript . '"'
                . ' -sDEVICE=pdfwrite'
                . ' -dCompatibilityLevel=1.4'
                . ' -dPDFSETTINGS=/ebook'
                . ' -dNOPAUSE'
                . ' -dQUIET'
                . ' -dBATCH'
                . ' -sOutputFile="' . $outputPath . '"'
                . ' "' . $inputPath . '"';

            // Execute Ghostscript
            exec($command, $output, $returnCode);

            /*
            |--------------------------------------------------------------------------
            | Check result
            |--------------------------------------------------------------------------
            */

            if ($returnCode !== 0 || !file_exists($outputPath)) {

                return back()->withErrors([
                    'pdf' => 'PDF compression failed.'
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Download compressed PDF
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