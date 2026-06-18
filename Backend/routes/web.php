<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth;
use App\Http\Controllers\Front;
use App\Http\Controllers\User;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// ── Landing Page ──────────────────────────────────────────────────────────
Route::get('/', [Front\HomeController::class, 'index'])->name('home');

// ── Loader.lua — diakses executor Roblox via HttpGet ──────────────────────
Route::get('/Loader.lua', [Front\LoaderController::class, 'serve'])->name('loader.lua');
Route::get('/loader.lua', [Front\LoaderController::class, 'serve']);

// ── Script serve via token sekali pakai ───────────────────────────────────
Route::get('/s/{token}', [Front\ScriptServeController::class, 'serve'])->name('script.serve');

// ── Modul Lua — hanya dengan token sesi dari lisensi aktif ────────────────
Route::get('/modules/{token}/{path}', [Front\ModuleServeController::class, 'serve'])
    ->where('token', '[a-f0-9]{64}')
    ->where('path', '.*')
    ->middleware('throttle:120,1')
    ->name('modules.serve');

// ── Autentikasi (Guest only) ──────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login', [Auth\LoginController::class, 'show'])->name('login');
    Route::post('/login', [Auth\LoginController::class, 'store'])->name('login.store');

    Route::get('/register', [Auth\RegisterController::class, 'show'])->name('register');
    Route::post('/register', [Auth\RegisterController::class, 'store'])->name('register.store');

    Route::get('/forgot-password', [Auth\ForgotPasswordController::class, 'show'])->name('password.request');
    Route::post('/forgot-password', [Auth\ForgotPasswordController::class, 'store'])->name('password.email');

    Route::get('/reset-password/{token}', [Auth\ResetPasswordController::class, 'show'])->name('password.reset');
    Route::post('/reset-password', [Auth\ResetPasswordController::class, 'store'])->name('password.update');
});

// ── Logout ────────────────────────────────────────────────────────────────
Route::post('/logout', [Auth\LogoutController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

// ── Dashboard Admin ───────────────────────────────────────────────────────
Route::prefix('admin')
    ->middleware(['auth', 'active', 'admin'])
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [Admin\DashboardController::class, 'index'])->name('dashboard');

        // Lisensi
        Route::get('/licenses', [Admin\LicenseController::class, 'index'])->name('licenses.index');
        Route::get('/licenses/export', [Admin\LicenseController::class, 'export'])->name('licenses.export');
        Route::post('/licenses', [Admin\LicenseController::class, 'store'])->name('licenses.store');
        Route::post('/licenses/bulk', [Admin\LicenseController::class, 'storeBulk'])->name('licenses.bulk');
        Route::get('/licenses/search-users', [Admin\LicenseController::class, 'searchUsers'])->name('licenses.search-users');
        Route::get('/licenses/{license}', [Admin\LicenseController::class, 'show'])->name('licenses.show');
        Route::put('/licenses/{license}', [Admin\LicenseController::class, 'update'])->name('licenses.update');
        Route::delete('/licenses/{license}', [Admin\LicenseController::class, 'destroy'])->name('licenses.destroy');
        Route::post('/licenses/{license}/reset-hwid', [Admin\LicenseController::class, 'resetHwid'])->name('licenses.reset-hwid');

        // Produk
        Route::resource('products', Admin\ProductController::class)->except(['show', 'create', 'edit']);
        Route::post('/products/github/inspect', [Admin\ProductController::class, 'inspectGithub'])->name('products.github.inspect');
        Route::post('/products/check-availability', [Admin\ProductController::class, 'checkAvailability'])->name('products.check-availability');
        Route::get('/products/github/status', [Admin\ProductController::class, 'githubStatus'])->name('products.github.status');
        Route::post('/products/{product}/refresh-script', [Admin\ProductController::class, 'refreshScript'])->name('products.refresh-script');

        // User
        Route::get('/users', [Admin\UserController::class, 'index'])->name('users.index');
        Route::get('/users/{user}', [Admin\UserController::class, 'show'])->name('users.show');
        Route::patch('/users/{user}/toggle-active', [Admin\UserController::class, 'toggleActive'])->name('users.toggle-active');

        // Log API
        Route::get('/api-logs', [Admin\ApiLogController::class, 'index'])->name('api-logs.index');
        Route::get('/api-logs/{apiLog}', [Admin\ApiLogController::class, 'show'])->name('api-logs.show');

        // Aktivitas
        Route::get('/activities', [Admin\ActivityController::class, 'index'])->name('activities.index');

        // Test Inject (debug tool)
        Route::get('/inject-test', [Admin\InjectTestController::class, 'index'])->name('inject-test.index');
        Route::post('/inject-test/run', [Admin\InjectTestController::class, 'run'])->name('inject-test.run');
    });

// ── Dashboard User ────────────────────────────────────────────────────────
Route::prefix('user')
    ->middleware(['auth', 'active'])
    ->name('user.')
    ->group(function () {
        Route::get('/dashboard', [User\DashboardController::class, 'index'])->name('dashboard');

        // Lisensi
        Route::get('/licenses', [User\LicenseController::class, 'index'])->name('licenses.index');
        Route::post('/licenses/{license}/reset-hwid', [User\LicenseController::class, 'resetHwid'])->name('licenses.reset-hwid');
        Route::get('/licenses/{license}/download', [User\LicenseController::class, 'download'])->name('licenses.download');

        // Aktivitas
        Route::get('/activities', [User\ActivityController::class, 'index'])->name('activities.index');

        // Profil
        Route::get('/profile', [User\ProfileController::class, 'show'])->name('profile.show');
        Route::put('/profile', [User\ProfileController::class, 'update'])->name('profile.update');
        Route::put('/profile/password', [User\ProfileController::class, 'updatePassword'])->name('profile.password');
    });
