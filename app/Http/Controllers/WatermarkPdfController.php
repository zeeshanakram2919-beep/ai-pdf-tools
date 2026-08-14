<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use setasign\Fpdi\Fpdi;

$fpdfPath = base_path('vendor/setasign/fpdi/fpdf.php');

if (!file_exists($fpdfPath)) {
    throw new \RuntimeException(
        'FPDF library not found: ' . $fpdfPath
    );
}

require_once $fpdfPath;

class WatermarkPdfController extends Controller
{
    public function index()
{
    return view('watermark-pdf');
}

    public function watermark(Request $request)
    {
        $request->validate([
            'pdf' => 'required|file|mimes:pdf|max:20480',
            'watermark' => 'required|string|max:200',
            'font_size' => 'nullable|integer|min:8|max:100',
        ]);

        $outputPath = null;

        try {
            $file = $request->file('pdf');

            $inputPath = $file->getRealPath();

            if (!$inputPath || !file_exists($inputPath)) {
                throw new \Exception('Uploaded PDF could not be found.');
            }

            $watermarkText = trim($request->input('watermark'));

            if ($watermarkText === '') {
                throw new \Exception('Watermark text cannot be empty.');
            }

            $fontSize = (int) $request->input('font_size', 30);

            $pdf = new Fpdi();

            $pdf->SetAutoPageBreak(false);

            $pageCount = $pdf->setSourceFile($inputPath);

            if ($pageCount < 1) {
                throw new \Exception('PDF does not contain any pages.');
            }

            for ($pageNumber = 1; $pageNumber <= $pageCount; $pageNumber++) {

                $templateId = $pdf->importPage($pageNumber);

                $size = $pdf->getTemplateSize($templateId);

                $width = $size['width'];
                $height = $size['height'];

                $orientation = $width > $height ? 'L' : 'P';

                $pdf->AddPage(
                    $orientation,
                    [$width, $height]
                );

                $pdf->useTemplate(
                    $templateId,
                    0,
                    0,
                    $width,
                    $height
                );

                /*
                 * Watermark font
                 */
                $pdf->SetFont(
                    'Helvetica',
                    'B',
                    $fontSize
                );

                /*
                 * Light grey watermark
                 */
                $pdf->SetTextColor(
                    210,
                    210,
                    210
                );

                /*
                 * Calculate text position
                 */
                $textWidth = $pdf->GetStringWidth(
                    $watermarkText
                );

                $x = ($width - $textWidth) / 2;
                $y = ($height / 2) - ($fontSize / 3);

                /*
                 * Write watermark
                 */
                $pdf->SetXY($x, $y);

                $pdf->Cell(
                    $textWidth,
                    $fontSize,
                    $watermarkText,
                    0,
                    0,
                    'C'
                );
            }

            $fileName =
                'watermarked-pdf-' .
                date('Ymd-His') .
                '.pdf';

            $storagePath = storage_path('app');

            if (!is_dir($storagePath)) {
                mkdir(
                    $storagePath,
                    0777,
                    true
                );
            }

            $outputPath =
                $storagePath .
                DIRECTORY_SEPARATOR .
                $fileName;

            $pdf->Output(
                $outputPath,
                'F'
            );

            if (
                !file_exists($outputPath) ||
                filesize($outputPath) <= 0
            ) {
                throw new \Exception(
                    'Watermarked PDF could not be created.'
                );
            }

            return response()
                ->download(
                    $outputPath,
                    $fileName
                )
                ->deleteFileAfterSend(true);

        } catch (\Throwable $e) {

            if (
                $outputPath &&
                file_exists($outputPath)
            ) {
                @unlink($outputPath);
            }

            return back()->withErrors([
                'pdf' =>
                    'PDF watermark failed: ' .
                    $e->getMessage()
            ]);
        }
    }
}
