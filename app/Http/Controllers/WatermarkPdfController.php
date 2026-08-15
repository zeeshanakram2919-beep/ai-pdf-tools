<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

require_once app_path('Libraries/FPDF/fpdf.php');

use setasign\Fpdi\Fpdi;

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
                throw new \RuntimeException(
                    'Uploaded PDF could not be found.'
                );
            }

            $watermarkText = trim(
                $request->input('watermark')
            );

            if ($watermarkText === '') {
                throw new \RuntimeException(
                    'Watermark text cannot be empty.'
                );
            }

            $fontSize = (int) $request->input(
                'font_size',
                30
            );

            $pdf = new Fpdi();

            $pdf->SetAutoPageBreak(false);
            $pdf->SetMargins(0, 0, 0);
            $pdf->SetCompression(true);

            $pageCount = $pdf->setSourceFile(
                $inputPath
            );

            if ($pageCount < 1) {
                throw new \RuntimeException(
                    'PDF does not contain any pages.'
                );
            }

            for (
                $pageNumber = 1;
                $pageNumber <= $pageCount;
                $pageNumber++
            ) {

                $templateId = $pdf->importPage(
                    $pageNumber
                );

                $size = $pdf->getTemplateSize(
                    $templateId
                );

                $width = (float) $size['width'];
                $height = (float) $size['height'];

                if ($width <= 0 || $height <= 0) {
                    throw new \RuntimeException(
                        'Invalid PDF page dimensions.'
                    );
                }

                $orientation = $width > $height
                    ? 'L'
                    : 'P';

                $pdf->AddPage(
                    $orientation,
                    [
                        $width,
                        $height,
                    ]
                );

                /*
                 * Place the complete original PDF page.
                 */
                $pdf->useTemplate(
                    $templateId,
                    0,
                    0,
                    $width,
                    $height,
                    false
                );

                /*
                 * Watermark font.
                 */
                $pdf->SetFont(
                    'Helvetica',
                    'B',
                    $fontSize
                );

                /*
                 * Light grey watermark.
                 */
                $pdf->SetTextColor(
                    210,
                    210,
                    210
                );

                /*
                 * Center watermark horizontally.
                 */
                $textWidth = $pdf->GetStringWidth(
                    $watermarkText
                );

                $x = ($width - $textWidth) / 2;

                $y = ($height / 2) - ($fontSize / 3);

                $pdf->SetXY(
                    $x,
                    $y
                );

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
                '-' .
                uniqid() .
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
                'F',
                $outputPath
            );

            if (
                !file_exists($outputPath) ||
                filesize($outputPath) <= 0
            ) {
                throw new \RuntimeException(
                    'Watermarked PDF could not be created.'
                );
            }

            return response()
                ->download(
                    $outputPath,
                    $fileName,
                    [
                        'Content-Type' => 'application/pdf',
                    ]
                )
                ->deleteFileAfterSend(true);

        } catch (\Throwable $e) {

            if (
                $outputPath &&
                file_exists($outputPath)
            ) {
                @unlink($outputPath);
            }

            return back()
                ->withInput()
                ->withErrors([
                    'pdf' =>
                        'PDF watermark failed: ' .
                        $e->getMessage(),
                ]);
        }
    }
}