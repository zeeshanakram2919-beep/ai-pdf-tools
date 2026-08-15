<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use setasign\Fpdi\Fpdi;

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

        try {

            /*
            |--------------------------------------------------------------------------
            | Load FPDF Manually
            |--------------------------------------------------------------------------
            */

           

            require

            /*
            |--------------------------------------------------------------------------
            | Create FPDI PDF
            |--------------------------------------------------------------------------
            */

            $pdf = new Fpdi();

            $pdf->SetAutoPageBreak(false);


            /*
            |--------------------------------------------------------------------------
            | Process Images
            |--------------------------------------------------------------------------
            */

            foreach ($request->file('images') as $image) {

                $imagePath = $image->getRealPath();

                if (!file_exists($imagePath)) {
                    throw new \Exception(
                        'Uploaded image not found.'
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Get Image Size
                |--------------------------------------------------------------------------
                */

                $imageInfo = getimagesize($imagePath);

                if ($imageInfo === false) {
                    throw new \Exception(
                        'Invalid JPG image.'
                    );
                }


                $imageWidth = $imageInfo[0];
                $imageHeight = $imageInfo[1];


                /*
                |--------------------------------------------------------------------------
                | A4 Page
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
                | Calculate Image Size
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
                | Add A4 Page
                |--------------------------------------------------------------------------
                */

                $pdf->AddPage(
                    $orientation,
                    'A4'
                );


                /*
                |--------------------------------------------------------------------------
                | Center Image
                |--------------------------------------------------------------------------
                */

                $x =
                    ($pageWidth - $pdfWidth) / 2;

                $y =
                    ($pageHeight - $pdfHeight) / 2;


                /*
                |--------------------------------------------------------------------------
                | Add JPG
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
            | File Name
            |--------------------------------------------------------------------------
            */

            $fileName =
                'jpg-to-pdf-' .
                date('Ymd-His') .
                '.pdf';


            /*
            |--------------------------------------------------------------------------
            | Storage Path
            |--------------------------------------------------------------------------
            */

            $storagePath =
                storage_path('app');


            if (!is_dir($storagePath)) {
                mkdir(
                    $storagePath,
                    0777,
                    true
                );
            }


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
                $filePath,
                'F'
            );


            /*
            |--------------------------------------------------------------------------
            | Check PDF
            |--------------------------------------------------------------------------
            */

            if (
                !file_exists($filePath) ||
                filesize($filePath) <= 0
            ) {

                throw new \Exception(
                    'PDF file could not be created.'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Download
            |--------------------------------------------------------------------------
            */

            return response()
                ->download(
                    $filePath,
                    $fileName
                )
                ->deleteFileAfterSend(true);


        } catch (\Throwable $e) {

            return back()->withErrors([
                'images' =>
                    'JPG to PDF conversion failed: ' .
                    $e->getMessage()
            ]);
        }
    }
}