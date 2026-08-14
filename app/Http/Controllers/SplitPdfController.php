<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use setasign\Fpdi\Fpdi;

// Load our working FPDF manually
require_once app_path('Libraries/FPDF/fpdf.php');

class SplitPdfController extends Controller
{
    public function index()
    {
        return view('split-pdf');
    }

    public function split(Request $request)
    {
        $request->validate([
            'pdf' => 'required|file|mimes:pdf|max:20480',
            'page' => 'required|integer|min:1',
        ]);

        try {
            // Create FPDI PDF object
            $pdf = new Fpdi();

            // Uploaded PDF
            $file = $request->file('pdf');

            // Load source PDF
            $pageCount = $pdf->setSourceFile($file->getRealPath());

            // Requested page
            $pageNumber = (int) $request->page;

            // Check requested page exists
            if ($pageNumber > $pageCount) {
                return back()->withErrors([
                    'page' => "This PDF has only {$pageCount} pages."
                ]);
            }

            // Import selected page
            $templateId = $pdf->importPage($pageNumber);

            // Get original page size
            $size = $pdf->getTemplateSize($templateId);

            // Determine orientation
            $orientation = $size['width'] > $size['height']
                ? 'L'
                : 'P';

            // Add page with original dimensions
            $pdf->AddPage(
                $orientation,
                [
                    $size['width'],
                    $size['height']
                ]
            );

            // Place imported page
            $pdf->useTemplate($templateId);

            // Create unique filename
            $fileName = 'split-page-' . time() . '.pdf';

            // Storage directory
            $storagePath = storage_path('app');

            // Create storage directory if needed
            if (!is_dir($storagePath)) {
                mkdir($storagePath, 0777, true);
            }

            // Full file path
            $filePath = $storagePath . DIRECTORY_SEPARATOR . $fileName;

            // Save PDF
            $pdf->Output($filePath, 'F');

            // Verify file
            if (!file_exists($filePath)) {
                return back()->withErrors([
                    'pdf' => 'Split PDF could not be created.'
                ]);
            }

            // Download and delete after sending
            return response()
                ->download($filePath, $fileName)
                ->deleteFileAfterSend(true);

        } catch (\Throwable $e) {
            return back()->withErrors([
                'pdf' => 'PDF split failed: ' . $e->getMessage()
            ]);
        }
    }
}