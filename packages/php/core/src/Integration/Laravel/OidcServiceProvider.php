<?php

declare(strict_types=1);

namespace OidcClient\Integration\Laravel;

use Illuminate\Support\ServiceProvider;
use OidcClient\OidcClient;
use OidcClient\Integration\OidcService;

final class OidcServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(OidcService::class, function ($app) {
            $config = $app['config']->get('oidc') ?? [];
            return new OidcService($config);
        });

        // Keep backward compatibility: bind OidcClient to the underlying client
        $this->app->singleton(OidcClient::class, function ($app) {
            return $app->make(OidcService::class)->getClient();
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/config/oidc.php' => config_path('oidc.php'),
        ], 'oidc-config');
    }
}
