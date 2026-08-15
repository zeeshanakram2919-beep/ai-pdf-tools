<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

require_once app_path('Libraries/FPDF/fpdf.php');

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

            $pdf = new Fpdi();

            $pdf->SetAutoPageBreak(false);
            $pdf->SetMargins(0, 0, 0);
            $pdf->SetCompression(true);

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
                 * IMPORTANT
                 *
                 * We do NOT manually rotate the template.
                 * We create the page with the required rotation.
                 *
                 * This rotates the COMPLETE PDF page instead of
                 * moving the content outside the page boundaries.
                 */

                $pdf->AddPage(
                    $width > $height ? 'L' : 'P',
                    [$width, $height],
                    $angle
                );

                /*
                 * Put the COMPLETE original page onto the new page.
                 *
                 * No manual matrix.
                 * No negative coordinates.
                 * No X/Y translation.
                 */

                $pdf->useTemplate(
                    $template,
                    0,
                    0,
                    $width,
                    $height,
                    false
                );
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