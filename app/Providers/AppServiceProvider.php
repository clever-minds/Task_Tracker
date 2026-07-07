<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (\Illuminate\Support\Facades\DB::connection() instanceof \Illuminate\Database\SQLiteConnection) {
            \Illuminate\Support\Facades\DB::statement('PRAGMA foreign_keys = ON;');
        }

        if (env('VERCEL_ENV') || env('APP_ENV') === 'production') {
            $viewPath = '/tmp/storage/framework/views';
            if (!is_dir($viewPath)) {
                @mkdir($viewPath, 0755, true);
            }
            config(['view.compiled' => $viewPath]);
        }
    }
}
