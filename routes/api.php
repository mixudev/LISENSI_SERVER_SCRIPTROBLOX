<?php

use App\Http\Controllers\Api\LicenseController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — License Server
|--------------------------------------------------------------------------
|
| Semua endpoint menggunakan:
| - ForceJsonResponse → selalu return JSON
| - throttle:60,1     → rate limit 60 req/menit per IP (sesuai SRS)
|
*/

Route::prefix('license')
    ->middleware(['api', 'throttle:api'])
    ->controller(LicenseController::class)
    ->group(function () {
        Route::post('activate', 'activate')->name('api.license.activate');
        Route::post('check', 'check')->name('api.license.check');
        Route::post('inject', 'inject')->name('api.license.inject');
        Route::get('get', 'getScript')->name('api.license.get');
    });
