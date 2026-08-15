<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use setasign\Fpdi\Fpdi;

/*
|--------------------------------------------------------------------------
| Load FPDF required by FPDI
|--------------------------------------------------------------------------
|
| Your current project has FPDI installed, but setasign/fpdf is not
| currently installed as a Composer package. Therefore we temporarily
| load the FPDF copy bundled with FPDI.
|
*/

$fpdfPath = base_path('vendor/setasign/fpdi/fpdf.php');

if (!file_exists($fpdfPath)) {
    throw new \RuntimeException(
        'FPDF library not found: ' . $fpdfPath
    );
}

require_once $fpdfPath;


class WatermarkPdfController extends Controller
{
    /**
     * Show watermark page
     */
    public function index()
    {
        return view('watermark-pdf');
    }

    /**
     * Add watermark to PDF
     */
    public function watermark(Request $request)
    {
        $request->validate([
            'pdf' => 'required|file|mimes:pdf|max:20480',
            'watermark' => 'required|string|max:200',
            'font_size' => 'nullable|integer|min:8|max:100',
        ]);

        $outputPath = null;

        try {

            /*
            |--------------------------------------------------------------------------
            | Uploaded PDF
            |--------------------------------------------------------------------------
            */

            $file = $request->file('pdf');

            $inputPath = $file->getRealPath();

            if (!$inputPath || !file_exists($inputPath)) {
                throw new \RuntimeException(
                    'Uploaded PDF could not be found.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Watermark text
            |--------------------------------------------------------------------------
            */

            $watermarkText = trim(
                $request->input('watermark')
            );

            if ($watermarkText === '') {
                throw new \RuntimeException(
                    'Watermark text cannot be empty.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Font size
            |--------------------------------------------------------------------------
            */

            $fontSize = (int) $request->input(
                'font_size',
                30
            );

            /*
            |--------------------------------------------------------------------------
            | Create FPDI
            |--------------------------------------------------------------------------
            */

            $pdf = new Fpdi();

            $pdf->SetAutoPageBreak(false);

            /*
            |--------------------------------------------------------------------------
            | Read source PDF
            |--------------------------------------------------------------------------
            */

            $pageCount = $pdf->setSourceFile(
                $inputPath
            );

            if ($pageCount < 1) {
                throw new \RuntimeException(
                    'PDF does not contain any pages.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Process every page
            |--------------------------------------------------------------------------
            */

            for (
                $pageNumber = 1;
                $pageNumber <= $pageCount;
                $pageNumber++
            ) {

                /*
                |--------------------------------------------------------------------------
                | Import page
                |--------------------------------------------------------------------------
                */

                $templateId = $pdf->importPage(
                    $pageNumber
                );

                /*
                |--------------------------------------------------------------------------
                | Get original page size
                |--------------------------------------------------------------------------
                */

                $size = $pdf->getTemplateSize(
                    $templateId
                );

                $width = $size['width'];
                $height = $size['height'];

                /*
                |--------------------------------------------------------------------------
                | Page orientation
                |--------------------------------------------------------------------------
                */

                $orientation = $width > $height
                    ? 'L'
                    : 'P';

                /*
                |--------------------------------------------------------------------------
                | Add page with original dimensions
                |--------------------------------------------------------------------------
                */

                $pdf->AddPage(
                    $orientation,
                    [
                        $width,
                        $height,
                    ]
                );

                /*
                |--------------------------------------------------------------------------
                | Place original page
                |--------------------------------------------------------------------------
                */

                $pdf->useTemplate(
                    $templateId,
                    0,
                    0,
                    $width,
                    $height
                );

                /*
                |--------------------------------------------------------------------------
                | Watermark font
                |--------------------------------------------------------------------------
                */

                $pdf->SetFont(
                    'Helvetica',
                    'B',
                    $fontSize
                );

                /*
                |--------------------------------------------------------------------------
                | Watermark colour
                |--------------------------------------------------------------------------
                */

                $pdf->SetTextColor(
                    210,
                    210,
                    210
                );

                /*
                |--------------------------------------------------------------------------
                | Calculate watermark position
                |--------------------------------------------------------------------------
                */

                $textWidth = $pdf->GetStringWidth(
                    $watermarkText
                );

                $x = ($width - $textWidth) / 2;

                $y = ($height / 2)
                    - ($fontSize / 3);

                /*
                |--------------------------------------------------------------------------
                | Write watermark
                |--------------------------------------------------------------------------
                */

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

            /*
            |--------------------------------------------------------------------------
            | Output filename
            |--------------------------------------------------------------------------
            */

            $fileName =
                'watermarked-pdf-' .
                date('Ymd-His') .
                '.pdf';

            /*
            |--------------------------------------------------------------------------
            | Storage directory
            |--------------------------------------------------------------------------
            */

            $storagePath = storage_path('app');

            if (!is_dir($storagePath)) {
                mkdir(
                    $storagePath,
                    0777,
                    true
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Output path
            |--------------------------------------------------------------------------
            */

            $outputPath =
                $storagePath .
                DIRECTORY_SEPARATOR .
                $fileName;

            /*
            |--------------------------------------------------------------------------
            | Save PDF
            |--------------------------------------------------------------------------
            */

            $pdf->Output(
                $outputPath,
                'F'
            );

            /*
            |--------------------------------------------------------------------------
            | Verify output
            |--------------------------------------------------------------------------
            */

            if (
                !file_exists($outputPath) ||
                filesize($outputPath) <= 0
            ) {
                throw new \RuntimeException(
                    'Watermarked PDF could not be created.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Download
            |--------------------------------------------------------------------------
            */

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

            /*
            |--------------------------------------------------------------------------
            | Cleanup
            |--------------------------------------------------------------------------
            */

            if (
                $outputPath &&
                file_exists($outputPath)
            ) {
                @unlink($outputPath);
            }

            /*
            |--------------------------------------------------------------------------
            | Error
            |--------------------------------------------------------------------------
            */

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