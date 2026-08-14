<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use setasign\Fpdi\Fpdi;

require_once base_path('vendor/setasign/fpdi/fpdf.php');

class MergePdfController extends Controller
{
    public function index()
    {
        return view('merge-pdf');
    }

    public function merge(Request $request)
    {
        // Validate uploaded PDF files
        $request->validate([
            'pdfs' => 'required|array|min:1',
            'pdfs.*' => 'required|file|mimes:pdf|max:20480',
        ]);

        try {
            // Create FPDI PDF object
            $pdf = new Fpdi();

            foreach ($request->file('pdfs') as $file) {

                // Set source PDF
                $pageCount = $pdf->setSourceFile($file->getRealPath());

                // Import every page
                for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {

                    // Import page
                    $templateId = $pdf->importPage($pageNo);

                    // Get original page size
                    $size = $pdf->getTemplateSize($templateId);

                    // Determine orientation
                    $orientation = $size['width'] > $size['height']
                        ? 'L'
                        : 'P';

                    // Add page using original dimensions
                    $pdf->AddPage(
                        $orientation,
                        [
                            $size['width'],
                            $size['height']
                        ]
                    );

                    // Place imported page
                    $pdf->useTemplate($templateId);
                }
            }

            // Create unique file name
            $fileName = 'merged-' . time() . '.pdf';

            // Storage directory
            $storagePath = storage_path('app');

            // Create directory if it doesn't exist
            if (!is_dir($storagePath)) {
                mkdir($storagePath, 0777, true);
            }

            // Full file path
            $filePath = $storagePath . DIRECTORY_SEPARATOR . $fileName;

            // Save merged PDF
            $pdf->Output($filePath, 'F');

            // Check if file was created
            if (!file_exists($filePath)) {
                return back()->withErrors([
                    'pdfs' => 'Merged PDF could not be created.'
                ]);
            }

            // Download merged PDF
            return response()
                ->download($filePath, $fileName)
                ->deleteFileAfterSend(true);

        } catch (\Throwable $e) {

            return back()->withErrors([
                'pdfs' => 'PDF merge failed: ' . $e->getMessage()
            ]);
        }
    }
}