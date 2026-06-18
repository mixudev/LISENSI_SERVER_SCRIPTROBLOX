<?php

namespace App\Providers;

use App\Repositories\LicenseRepository;
use App\Repositories\ProductRepository;
use App\Repositories\UserRepository;
use App\Services\ApiLogService;
use App\Services\HwidService;
use App\Services\LicenseService;
use App\Services\ModuleAccessService;
use App\Services\ScriptService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Repositories — singleton untuk efisiensi
        $this->app->singleton(LicenseRepository::class);
        $this->app->singleton(ProductRepository::class);
        $this->app->singleton(UserRepository::class);

        // Services
        $this->app->singleton(ApiLogService::class);
        $this->app->singleton(HwidService::class);
        $this->app->singleton(LicenseService::class);
        $this->app->singleton(ModuleAccessService::class);
        $this->app->singleton(ScriptService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();

        if ($this->app->isProduction()) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
    }

    /**
     * Konfigurasi rate limiter sesuai SRS: 60 request per menit per IP.
     */
    private function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->ip())->response(function () {
                return response()->json([
                    'status' => false,
                    'message' => 'Too many requests. Please try again later.',
                ], 429);
            });
        });
    }
}
