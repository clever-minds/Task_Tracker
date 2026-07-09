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

        file_put_contents('php://stderr', 'DIAG register() ran | default=' . var_export($default, true) . ' is_dir=' . var_export(is_dir($default), true) . ' is_writable=' . var_export(is_writable($default), true) . ' final=' . var_export(config('view.compiled'), true) . "\n");
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
