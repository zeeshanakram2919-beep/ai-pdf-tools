<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use setasign\Fpdi\Fpdi;

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
            $file = $request->file('pdf');

            if (!$file || !$file->isValid()) {
                return back()->withErrors([
                    'pdf' => 'Please upload a valid PDF file.'
                ]);
            }

            $pdf = new Fpdi();

            $pageCount = $pdf->setSourceFile(
                $file->getRealPath()
            );

            $pageNumber = (int) $request->input('page');

            if ($pageNumber > $pageCount) {
                return back()->withErrors([
                    'page' => "This PDF has only {$pageCount} pages."
                ]);
            }

            $templateId = $pdf->importPage($pageNumber);

            $size = $pdf->getTemplateSize($templateId);

            $orientation = ($size['width'] > $size['height'])
                ? 'L'
                : 'P';

            $pdf->AddPage(
                $orientation,
                [
                    $size['width'],
                    $size['height']
                ]
            );

            $pdf->useTemplate($templateId);

            $fileName = 'split-page-' . time() . '-' . uniqid() . '.pdf';

            $storagePath = storage_path('app');

            if (!is_dir($storagePath)) {
                mkdir($storagePath, 0777, true);
            }

            $filePath = $storagePath . DIRECTORY_SEPARATOR . $fileName;

            $pdf->Output($filePath, 'F');

            if (!file_exists($filePath) || filesize($filePath) === 0) {
                return back()->withErrors([
                    'pdf' => 'Split PDF could not be created.'
                ]);
            }

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
