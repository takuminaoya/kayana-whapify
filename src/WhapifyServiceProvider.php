<?php

namespace Kayana\Whapify;

use Illuminate\Support\ServiceProvider;

class WhapifyServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../configs/config.php' => config_path('whapify.php')
        ], 'kayana-whapify');
    }
}
