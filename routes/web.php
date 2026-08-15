<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\MergePdfController;
use App\Http\Controllers\SplitPdfController;
use App\Http\Controllers\CompressPdfController;
use App\Http\Controllers\JpgToPdfController;
use App\Http\Controllers\PdfToJpgController;
use App\Http\Controllers\RotatePdfController;
use App\Http\Controllers\WatermarkPdfController;


/*
|--------------------------------------------------------------------------
| Home
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
})->name('home');


/*
|--------------------------------------------------------------------------
| Merge PDF
|--------------------------------------------------------------------------
*/

Route::get('/merge-pdf', [MergePdfController::class, 'index'])
    ->name('merge-pdf');

Route::post('/merge-pdf', [MergePdfController::class, 'merge'])
    ->name('merge-pdf.process');


/*
|--------------------------------------------------------------------------
| Split PDF
|--------------------------------------------------------------------------
*/

Route::get('/split-pdf', [SplitPdfController::class, 'index'])
    ->name('split-pdf');

Route::post('/split-pdf', [SplitPdfController::class, 'split'])
    ->name('split-pdf.process');


/*
|--------------------------------------------------------------------------
| Compress PDF
|--------------------------------------------------------------------------
*/

Route::get('/compress-pdf', [CompressPdfController::class, 'index'])
    ->name('compress-pdf');

Route::post('/compress-pdf', [CompressPdfController::class, 'compress'])
    ->name('compress-pdf.process');


/*
|--------------------------------------------------------------------------
| JPG To PDF
|--------------------------------------------------------------------------
*/

Route::get('/jpg-to-pdf', [JpgToPdfController::class, 'index'])
    ->name('jpg-to-pdf');

Route::post('/jpg-to-pdf', [JpgToPdfController::class, 'convert'])
    ->name('jpg-to-pdf.process');


/*
|--------------------------------------------------------------------------
| PDF To JPG
|--------------------------------------------------------------------------
*/

Route::get('/pdf-to-jpg', [PdfToJpgController::class, 'index'])
    ->name('pdf-to-jpg');

Route::post('/pdf-to-jpg', [PdfToJpgController::class, 'convert'])
    ->name('pdf-to-jpg.process');


/*
|--------------------------------------------------------------------------
| Rotate PDF
|--------------------------------------------------------------------------
*/

Route::get('/rotate-pdf', [RotatePdfController::class, 'index'])
    ->name('rotate-pdf');

Route::post('/rotate-pdf', [RotatePdfController::class, 'rotate'])
    ->name('rotate-pdf.process');


/*
|--------------------------------------------------------------------------
| Watermark PDF
|--------------------------------------------------------------------------
*/

Route::get('/watermark-pdf', [WatermarkPdfController::class, 'index'])
    ->name('watermark-pdf');

Route::post('/watermark-pdf', [WatermarkPdfController::class, 'watermark'])
    ->name('watermark-pdf.process');


/*
|--------------------------------------------------------------------------
| XML Sitemap
|--------------------------------------------------------------------------
*/

Route::get('/sitemap.xml', function () {

    $urls = [
        url('/'),
        url('/merge-pdf'),
        url('/split-pdf'),
        url('/compress-pdf'),
        url('/jpg-to-pdf'),
        url('/pdf-to-jpg'),
        url('/rotate-pdf'),
        url('/watermark-pdf'),
    ];

    $xml = '<?xml version="1.0" encoding="UTF-8"?>';

    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

    foreach ($urls as $url) {

        $xml .= '<url>';

        $xml .= '<loc>'
            . htmlspecialchars($url, ENT_XML1, 'UTF-8')
            . '</loc>';

        $xml .= '</url>';
    }

    $xml .= '</urlset>';

    return response($xml, 200)
        ->header('Content-Type', 'application/xml');
})->name('sitemap');