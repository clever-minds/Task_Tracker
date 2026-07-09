<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $default = storage_path('framework/views');
        $viewPath = (is_dir($default) && is_writable($default)) ? $default : '/tmp/storage/framework/views';

        if (!is_dir($viewPath)) {
            @mkdir($viewPath, 0755, true);
        }

        config(['view.compiled' => $viewPath]);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('database.default') === 'sqlite') {
            \Illuminate\Support\Facades\DB::statement('PRAGMA foreign_keys = ON;');
        }
    }
}
