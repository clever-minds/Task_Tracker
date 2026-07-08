<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        error_log('DIAG: AppServiceProvider::register() ENTERED');

        $default = storage_path('framework/views');
        $viewPath = (is_dir($default) && is_writable($default)) ? $default : '/tmp/storage/framework/views';

        if (!is_dir($viewPath)) {
            @mkdir($viewPath, 0755, true);
        }

        config(['view.compiled' => $viewPath]);

        error_log('DIAG: view.compiled set to ' . var_export(config('view.compiled'), true) . ' | default=' . var_export($default, true) . ' is_dir=' . var_export(is_dir($default), true) . ' is_writable=' . var_export(is_writable($default), true));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (\Illuminate\Support\Facades\DB::connection() instanceof \Illuminate\Database\SQLiteConnection) {
            \Illuminate\Support\Facades\DB::statement('PRAGMA foreign_keys = ON;');
        }
    }
}
