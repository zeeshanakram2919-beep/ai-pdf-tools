<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use setasign\Fpdi\Fpdi;

class MergePdfController extends Controller
{
    /**
     * Show Merge PDF page
     */
    public function index()
    {
        return view('merge-pdf');
    }

    /**
     * Merge PDF files
     */
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
                        [$size['width'], $size['height']]
                    );

                    $pdf->useTemplate($templateId);
                }
            }

            $outputDirectory = storage_path('app/merged');

            if (!is_dir($outputDirectory)) {
                mkdir($outputDirectory, 0755, true);
            }

            $outputPath = $outputDirectory . '/merged-' . uniqid() . '.pdf';

            $pdf->Output('F', $outputPath);

            return response()->download(
                $outputPath,
                'merged.pdf',
                [
                    'Content-Type' => 'application/pdf',
                ]
            )->deleteFileAfterSend(true);

        } catch (\Throwable $e) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    'PDF merge failed: ' . $e->getMessage()
                );
        }
    }
}