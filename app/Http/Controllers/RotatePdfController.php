<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use setasign\Fpdi\Fpdi;

// Load the working FPDF library from the application
require_once app_path('Libraries/FPDF/fpdf.php');

class RotatePdfController extends Controller
{
    /**
     * Show Rotate PDF page
     */
    public function index()
    {
        return view('rotate-pdf');
    }

    /**
     * Rotate PDF
     */
    public function rotate(Request $request)
    {
        $request->validate([
            'pdf' => 'required|file|mimes:pdf|max:20480',
            'angle' => 'required|integer|in:90,180,270',
        ]);

        $outputPath = null;

        try {
            // Get uploaded PDF
            $file = $request->file('pdf');

            if (!$file || !$file->isValid()) {
                return back()->withErrors([
                    'pdf' => 'Please upload a valid PDF file.'
                ]);
            }

            $inputPath = $file->getRealPath();

            if (!$inputPath || !file_exists($inputPath)) {
                throw new \Exception(
                    'Uploaded PDF could not be found.'
                );
            }

            // Rotation angle
            $angle = (int) $request->input('angle');

            // Create FPDI
            $pdf = new Fpdi();

            $pdf->SetAutoPageBreak(false);

            // Load source PDF
            $pageCount = $pdf->setSourceFile($inputPath);

            if ($pageCount < 1) {
                throw new \Exception(
                    'The uploaded PDF does not contain any pages.'
                );
            }

            // Process every page
            for ($pageNumber = 1; $pageNumber <= $pageCount; $pageNumber++) {

                // Import page
                $templateId = $pdf->importPage($pageNumber);

                // Get page size
                $size = $pdf->getTemplateSize($templateId);

                $width = (float) $size['width'];
                $height = (float) $size['height'];

                /*
                 * When rotating 90 or 270 degrees,
                 * swap the page dimensions so the
                 * complete PDF page remains visible.
                 */
                if ($angle === 90 || $angle === 270) {
                    $pageWidth = $height;
                    $pageHeight = $width;
                } else {
                    $pageWidth = $width;
                    $pageHeight = $height;
                }

                // Determine orientation
                $orientation = $pageWidth > $pageHeight
                    ? 'L'
                    : 'P';

                // Add new page
                $pdf->AddPage(
                    $orientation,
                    [$pageWidth, $pageHeight]
                );

                /*
                 * Rotate the imported page.
                 *
                 * FPDF/FPDI uses transformation methods
                 * provided by the installed FPDF library.
                 */

                if ($angle === 90) {

                    $pdf->Rotate(90, $pageWidth, 0);

                    $pdf->useTemplate(
                        $templateId,
                        0,
                        -$width,
                        $width,
                        $height
                    );

                    $pdf->Rotate(0);

                } elseif ($angle === 180) {

                    $pdf->Rotate(
                        180,
                        $pageWidth / 2,
                        $pageHeight / 2
                    );

                    $pdf->useTemplate(
                        $templateId,
                        0,
                        0,
                        $width,
                        $height
                    );

                    $pdf->Rotate(0);

                } elseif ($angle === 270) {

                    $pdf->Rotate(
                        270,
                        0,
                        $pageHeight
                    );

                    $pdf->useTemplate(
                        $templateId,
                        -$height,
                        0,
                        $width,
                        $height
                    );

                    $pdf->Rotate(0);
                }
            }

            // Unique output filename
            $fileName =
                'rotated-pdf-' .
                date('Ymd-His') .
                '-' .
                uniqid() .
                '.pdf';

            // Storage directory
            $storagePath = storage_path('app');

            if (!is_dir($storagePath)) {
                mkdir($storagePath, 0777, true);
            }

            // Final output path
            $outputPath =
                $storagePath .
                DIRECTORY_SEPARATOR .
                $fileName;

            // Generate PDF
            $pdf->Output(
                $outputPath,
                'F'
            );

            // Verify file
            if (
                !file_exists($outputPath) ||
                filesize($outputPath) <= 0
            ) {
                throw new \Exception(
                    'Rotated PDF could not be created.'
                );
            }

            // Download
            return response()
                ->download(
                    $outputPath,
                    $fileName
                )
                ->deleteFileAfterSend(true);

        } catch (\Throwable $e) {

            // Cleanup failed output
            if (
                $outputPath &&
                file_exists($outputPath)
            ) {
                @unlink($outputPath);
            }

            return back()->withErrors([
                'pdf' =>
                    'PDF rotation failed: ' .
                    $e->getMessage()
            ]);
        }
    }
}
