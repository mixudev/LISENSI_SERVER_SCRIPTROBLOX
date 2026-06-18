<?php

use App\Http\Controllers\Api\DiscordBotController;
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

/*
|--------------------------------------------------------------------------
| Discord Bot API — Machine-to-machine (Bearer token, NOT public)
|--------------------------------------------------------------------------
*/
Route::prefix('bot')
    ->middleware(['api', 'throttle:api', 'bot.auth'])
    ->controller(DiscordBotController::class)
    ->group(function () {
        Route::get('health', 'health')->name('api.bot.health');
        Route::get('stats', 'stats')->name('api.bot.stats');
        Route::get('script-template', 'scriptTemplate')->name('api.bot.script-template');
        Route::post('reset-hwid', 'resetHwid')->name('api.bot.reset-hwid');
        Route::post('generate', 'generate')->name('api.bot.generate');
    });
