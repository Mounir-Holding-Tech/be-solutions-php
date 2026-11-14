<?php

declare(strict_types=1);

namespace BeSolutions\Laravel;

use BeSolutions\Client;
use Illuminate\Support\ServiceProvider;

/** Registers the client as a singleton, configured from config/besolutions.php (or .env). */
final class BeSolutionsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/besolutions.php', 'besolutions');

        $this->app->singleton(Client::class, function ($app): Client {
            $config = $app['config']->get('besolutions', []);
            return new Client(array_filter([
                'base_url' => $config['base_url'] ?? null,
                'api_key' => $config['api_key'] ?? null,
                'project_key' => $config['project_key'] ?? null,
                'project_secret' => $config['project_secret'] ?? null,
                'timeout' => $config['timeout'] ?? null,
            ], static fn ($v) => $v !== null && $v !== ''));
        });
        $this->app->alias(Client::class, 'besolutions');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([__DIR__ . '/../../config/besolutions.php' => config_path('besolutions.php')], 'besolutions-config');
        }
    }
}
