<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use setasign\Fpdi\Fpdi;

require_once app_path('Libraries/FPDF/fpdf.php');

class MergePdfController extends Controller
{
    public function index()
    {
        return view('merge-pdf');
    }

    public function merge(Request $request)
    {
        $request->validate([
            'pdfs' => 'required|array|min:1',
            'pdfs.*' => 'required|file|mimes:pdf|max:20480',
        ]);

        try {
            $pdf = new Fpdi();

            foreach ($request->file('pdfs') as $file) {

                $pageCount = $pdf->setSourceFile(
                    $file->getRealPath()
                );

                for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {

                    $templateId = $pdf->importPage($pageNo);

                    $size = $pdf->getTemplateSize($templateId);

                    $orientation = $size['width'] > $size['height']
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
                }
            }

            $fileName = 'merged-' . time() . '.pdf';

            $storagePath = storage_path('app');

            if (!is_dir($storagePath)) {
                mkdir($storagePath, 0777, true);
            }

            $filePath = $storagePath . DIRECTORY_SEPARATOR . $fileName;

            $pdf->Output($filePath, 'F');

            if (!file_exists($filePath)) {
                return back()->withErrors([
                    'pdfs' => 'Merged PDF could not be created.'
                ]);
            }

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