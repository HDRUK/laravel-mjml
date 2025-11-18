<?php

namespace Hdruk\LaravelMjml\Providers;

use Illuminate\Support\ServiceProvider;
use Hdruk\LaravelMjml\Console\Commands\TestEmailTemplates;

/**
 * This file is part of the Laravel MJML package.
 * 
 * @author Loki Sinclair <loki.sinclair@hdruk.ac.uk> (C)
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 class LaravelMjmlServiceProvider extends ServiceProvider
 {
    /**
     * Bootstrap any package services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/mjml.php' => config_path('mjml.php'),
        ]);

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        if ($this->app->runningInConsole()) {
            $this->commands([
                TestEmailTemplates::class,
            ]);
        }
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {

    }
 }
