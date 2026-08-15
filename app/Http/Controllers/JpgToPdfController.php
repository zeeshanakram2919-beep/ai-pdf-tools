<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Load FPDF
|--------------------------------------------------------------------------
|
| Your project currently has the FPDF file bundled with FPDI.
| We load it once and use the FPDF class directly.
|
*/

$fpdfPath = base_path('vendor/setasign/fpdi/fpdf.php');

if (!file_exists($fpdfPath)) {
    throw new \RuntimeException(
        'FPDF library not found: ' . $fpdfPath
    );
}

require_once $fpdfPath;

class JpgToPdfController extends Controller
{
    /**
     * Show JPG to PDF page.
     */
    public function index()
    {
        return view('jpg-to-pdf');
    }

    /**
     * Convert JPG images to PDF.
     */
    public function convert(Request $request)
    {
        $request->validate([
            'images' => [
                'required',
                'array',
                'min:1',
            ],
            'images.*' => [
                'required',
                'image',
                'mimes:jpg,jpeg',
                'max:20480',
            ],
        ]);

        $filePath = null;

        try {

            /*
            |--------------------------------------------------------------------------
            | Create FPDF document
            |--------------------------------------------------------------------------
            */

            $pdf = new \FPDF();

            $pdf->SetAutoPageBreak(false);

            /*
            |--------------------------------------------------------------------------
            | Process all uploaded images
            |--------------------------------------------------------------------------
            */

            foreach ($request->file('images') as $image) {

                $imagePath = $image->getRealPath();

                if (
                    !$imagePath ||
                    !file_exists($imagePath)
                ) {
                    throw new \RuntimeException(
                        'Uploaded image could not be found.'
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Get image dimensions
                |--------------------------------------------------------------------------
                */

                $imageInfo = getimagesize($imagePath);

                if ($imageInfo === false) {
                    throw new \RuntimeException(
                        'Invalid JPG image.'
                    );
                }

                $imageWidth = $imageInfo[0];
                $imageHeight = $imageInfo[1];

                if (
                    $imageWidth <= 0 ||
                    $imageHeight <= 0
                ) {
                    throw new \RuntimeException(
                        'Invalid image dimensions.'
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | A4 orientation
                |--------------------------------------------------------------------------
                */

                if ($imageWidth >= $imageHeight) {

                    $pageWidth = 297;
                    $pageHeight = 210;
                    $orientation = 'L';

                } else {

                    $pageWidth = 210;
                    $pageHeight = 297;
                    $orientation = 'P';
                }

                /*
                |--------------------------------------------------------------------------
                | Margins
                |--------------------------------------------------------------------------
                */

                $margin = 10;

                $availableWidth =
                    $pageWidth - ($margin * 2);

                $availableHeight =
                    $pageHeight - ($margin * 2);

                /*
                |--------------------------------------------------------------------------
                | Maintain image aspect ratio
                |--------------------------------------------------------------------------
                */

                $ratio = min(
                    $availableWidth / $imageWidth,
                    $availableHeight / $imageHeight
                );

                $pdfWidth =
                    $imageWidth * $ratio;

                $pdfHeight =
                    $imageHeight * $ratio;

                /*
                |--------------------------------------------------------------------------
                | Add page
                |--------------------------------------------------------------------------
                */

                $pdf->AddPage(
                    $orientation,
                    'A4'
                );

                /*
                |--------------------------------------------------------------------------
                | Center image
                |--------------------------------------------------------------------------
                */

                $x =
                    ($pageWidth - $pdfWidth) / 2;

                $y =
                    ($pageHeight - $pdfHeight) / 2;

                /*
                |--------------------------------------------------------------------------
                | Add JPG image
                |--------------------------------------------------------------------------
                */

                $pdf->Image(
                    $imagePath,
                    $x,
                    $y,
                    $pdfWidth,
                    $pdfHeight,
                    'JPG'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Create storage directory
            |--------------------------------------------------------------------------
            */

            $storagePath = storage_path(
                'app' . DIRECTORY_SEPARATOR . 'jpg-to-pdf'
            );

            if (!is_dir($storagePath)) {
                mkdir(
                    $storagePath,
                    0755,
                    true
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Output filename
            |--------------------------------------------------------------------------
            */

            $fileName =
                'jpg-to-pdf-' .
                date('Ymd-His') .
                '-' .
                uniqid() .
                '.pdf';

            $filePath =
                $storagePath .
                DIRECTORY_SEPARATOR .
                $fileName;

            /*
            |--------------------------------------------------------------------------
            | Save PDF
            |--------------------------------------------------------------------------
            */

            $pdf->Output(
                'F',
                $filePath
            );

            /*
            |--------------------------------------------------------------------------
            | Verify output
            |--------------------------------------------------------------------------
            */

            if (
                !file_exists($filePath) ||
                filesize($filePath) <= 0
            ) {
                throw new \RuntimeException(
                    'PDF file could not be created.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Download PDF
            |--------------------------------------------------------------------------
            */

            return response()
                ->download(
                    $filePath,
                    'converted-images.pdf',
                    [
                        'Content-Type' =>
                            'application/pdf',
                    ]
                )
                ->deleteFileAfterSend(true);

        } catch (\Throwable $e) {

            /*
            |--------------------------------------------------------------------------
            | Cleanup failed output
            |--------------------------------------------------------------------------
            */

            if (
                $filePath &&
                file_exists($filePath)
            ) {
                @unlink($filePath);
            }

            /*
            |--------------------------------------------------------------------------
            | Return error
            |--------------------------------------------------------------------------
            */

            return back()
                ->withInput()
                ->withErrors([
                    'images' =>
                        'JPG to PDF conversion failed: ' .
                        $e->getMessage(),
                ]);
        }
    }
}