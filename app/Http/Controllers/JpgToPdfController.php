<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Load project FPDF library
|--------------------------------------------------------------------------
*/

$fpdfPath = app_path('Libraries/FPDF/fpdf.php');

if (!file_exists($fpdfPath)) {
    throw new \RuntimeException(
        'FPDF library not found: ' . $fpdfPath
    );
}

require_once $fpdfPath;

class JpgToPdfController extends Controller
{
    public function index()
    {
        return view('jpg-to-pdf');
    }

    public function convert(Request $request)
    {
        $request->validate([
            'images' => 'required|array|min:1',
            'images.*' => 'required|image|mimes:jpg,jpeg|max:20480',
        ]);

        $filePath = null;

        try {
            $pdf = new \FPDF();

            $pdf->SetAutoPageBreak(false);

            foreach ($request->file('images') as $image) {

                $imagePath = $image->getRealPath();

                if (!$imagePath || !file_exists($imagePath)) {
                    throw new \RuntimeException(
                        'Uploaded image could not be found.'
                    );
                }

                $imageInfo = getimagesize($imagePath);

                if ($imageInfo === false) {
                    throw new \RuntimeException(
                        'Invalid JPG image.'
                    );
                }

                $imageWidth = (int) $imageInfo[0];
                $imageHeight = (int) $imageInfo[1];

                if ($imageWidth <= 0 || $imageHeight <= 0) {
                    throw new \RuntimeException(
                        'Invalid image dimensions.'
                    );
                }

                if ($imageWidth >= $imageHeight) {
                    $pageWidth = 297;
                    $pageHeight = 210;
                    $orientation = 'L';
                } else {
                    $pageWidth = 210;
                    $pageHeight = 297;
                    $orientation = 'P';
                }

                $margin = 10;

                $availableWidth = $pageWidth - ($margin * 2);
                $availableHeight = $pageHeight - ($margin * 2);

                $ratio = min(
                    $availableWidth / $imageWidth,
                    $availableHeight / $imageHeight
                );

                $pdfWidth = $imageWidth * $ratio;
                $pdfHeight = $imageHeight * $ratio;

                $pdf->AddPage(
                    $orientation,
                    'A4'
                );

                $x = ($pageWidth - $pdfWidth) / 2;
                $y = ($pageHeight - $pdfHeight) / 2;

                $pdf->Image(
                    $imagePath,
                    $x,
                    $y,
                    $pdfWidth,
                    $pdfHeight,
                    'JPG'
                );
            }

            $storagePath = storage_path(
                'app' . DIRECTORY_SEPARATOR . 'jpg-to-pdf'
            );

            if (!is_dir($storagePath)) {
                mkdir($storagePath, 0755, true);
            }

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

            $pdf->Output(
                'F',
                $filePath
            );

            if (
                !file_exists($filePath) ||
                filesize($filePath) <= 0
            ) {
                throw new \RuntimeException(
                    'PDF file could not be created.'
                );
            }

            return response()
                ->download(
                    $filePath,
                    'converted-images.pdf',
                    [
                        'Content-Type' => 'application/pdf',
                    ]
                )
                ->deleteFileAfterSend(true);

        } catch (\Throwable $e) {

            if ($filePath && file_exists($filePath)) {
                @unlink($filePath);
            }

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