<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use setasign\Fpdi\Fpdi;

class RotatePdfController extends Controller
{
    public function index()
    {
        return view('rotate-pdf');
    }

    public function rotate(Request $request)
    {
        $request->validate([
            'pdf' => 'required|file|mimes:pdf|max:20480',
            'angle' => 'required|integer|in:90,180,270',
        ]);

        $inputPath = $request->file('pdf')->getRealPath();
        $angle = (int) $request->angle;

        try {

            /*
             * Load FPDF before FPDI
             */
            

            $pdf = new Fpdi();

            $pdf->SetAutoPageBreak(false);
            $pdf->SetMargins(0, 0, 0);

            $pageCount = $pdf->setSourceFile($inputPath);

            if ($pageCount < 1) {
                throw new \Exception('The uploaded PDF has no pages.');
            }

            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {

                $template = $pdf->importPage($pageNo);

                $size = $pdf->getTemplateSize($template);

                $width = (float) $size['width'];
                $height = (float) $size['height'];

                if ($width <= 0 || $height <= 0) {
                    throw new \Exception(
                        "Invalid PDF page size: {$width} x {$height}"
                    );
                }

                /*
                 * 180 DEGREE
                 */
                if ($angle === 180) {

                    $orientation = $width > $height ? 'L' : 'P';

                    $pdf->AddPage(
                        $orientation,
                        [$width, $height]
                    );

                    $pdf->useTemplate(
                        $template,
                        $width,
                        $height,
                        -$width,
                        -$height
                    );
                }

                /*
                 * 90 DEGREE
                 */
                elseif ($angle === 90) {

                    $newWidth = $height;
                    $newHeight = $width;

                    if ($newWidth <= 0 || $newHeight <= 0) {
                        throw new \Exception(
                            "Invalid rotated page size."
                        );
                    }

                    $orientation = $newWidth > $newHeight
                        ? 'L'
                        : 'P';

                    $pdf->AddPage(
                        $orientation,
                        [$newWidth, $newHeight]
                    );

                    /*
                     * Rotate clockwise.
                     *
                     * The translation is based on the NEW
                     * page height so the complete page remains
                     * inside the canvas.
                     */
                    $pdf->_out(
                        sprintf(
                            'q 0 1 -1 0 %.4F 0 cm',
                            $newWidth
                        )
                    );

                    $pdf->useTemplate(
                        $template,
                        0,
                        0,
                        $width,
                        $height
                    );

                    $pdf->_out('Q');
                }

                /*
                 * 270 DEGREE
                 */
                elseif ($angle === 270) {

                    $newWidth = $height;
                    $newHeight = $width;

                    if ($newWidth <= 0 || $newHeight <= 0) {
                        throw new \Exception(
                            "Invalid rotated page size."
                        );
                    }

                    $orientation = $newWidth > $newHeight
                        ? 'L'
                        : 'P';

                    $pdf->AddPage(
                        $orientation,
                        [$newWidth, $newHeight]
                    );

                    /*
                     * Rotate counter-clockwise.
                     */
                    $pdf->_out(
                        sprintf(
                            'q 0 -1 1 0 0 %.4F cm',
                            $newHeight
                        )
                    );

                    $pdf->useTemplate(
                        $template,
                        0,
                        0,
                        $width,
                        $height
                    );

                    $pdf->_out('Q');
                }
            }

            $fileName =
                'rotated-' .
                date('Ymd-His') .
                '-' .
                uniqid() .
                '.pdf';

            $outputPath = storage_path(
                'app' . DIRECTORY_SEPARATOR . $fileName
            );

            $pdf->Output(
                'F',
                $outputPath
            );

            if (
                !file_exists($outputPath) ||
                filesize($outputPath) <= 0
            ) {
                throw new \Exception(
                    'Rotated PDF was not created.'
                );
            }

            return response()
                ->download(
                    $outputPath,
                    $fileName
                )
                ->deleteFileAfterSend(true);

        } catch (\Throwable $e) {

            return back()->withErrors([
                'pdf' =>
                    'PDF rotation failed: ' .
                    $e->getMessage(),
            ]);
        }
    }
}