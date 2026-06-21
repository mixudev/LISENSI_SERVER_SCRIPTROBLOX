<?php

use App\Http\Controllers\Api\DiscordBotController;
use App\Http\Controllers\Api\LicenseController;
use App\Http\Controllers\Api\TicketController;
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
    ->group(function () {
        Route::controller(DiscordBotController::class)->group(function () {
            Route::get('health', 'health')->name('api.bot.health');
            Route::get('stats', 'stats')->name('api.bot.stats');
            Route::get('script-template', 'scriptTemplate')->name('api.bot.script-template');
            Route::post('reset-hwid', 'resetHwid')->name('api.bot.reset-hwid');
            Route::post('generate', 'generate')->name('api.bot.generate');
            Route::post('redeem', 'redeem')->name('api.bot.redeem');
            Route::post('link-roblox', 'linkRoblox')->name('api.bot.link-roblox');
            Route::get('link-roblox/url', 'robloxConnectUrl')->name('api.bot.link-roblox.url');
            Route::get('list-users', 'listUsers')->name('api.bot.list-users');
            Route::post('revoke', 'revokeKey')->name('api.bot.revoke');
            Route::get('server-stats', 'serverStats')->name('api.bot.server-stats');
            Route::get('discord-admins', 'listDiscordAdmins')->name('api.bot.discord-admins');
            Route::post('ai/chat', 'askAi')->name('api.bot.ai-chat');
        });

        Route::controller(TicketController::class)->group(function () {
            Route::post('tickets', 'create')->name('api.bot.tickets.create');
            Route::post('tickets/process', 'process')->name('api.bot.tickets.process');
            Route::post('tickets/close', 'close')->name('api.bot.tickets.close');
            Route::post('tickets/check-payment', 'checkPayment')->name('api.bot.tickets.check-payment');
            Route::get('tickets/{channel_id}', 'show')->name('api.bot.tickets.show');
        });
    });

Route::post('midtrans/callback', [TicketController::class, 'midtransCallback'])->name('api.midtrans.callback');
