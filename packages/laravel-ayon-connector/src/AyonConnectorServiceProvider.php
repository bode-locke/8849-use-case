<?php

namespace Benjamin\AyonConnector;

use Illuminate\Support\ServiceProvider;
use Benjamin\AyonConnector\Contracts\AyonClientInterface;
use Benjamin\AyonConnector\Services\AyonClient;

/**
 * Class AyonConnectorServiceProvider
 *
 * @package Benjamin\AyonConnector
 */
class AyonConnectorServiceProvider extends ServiceProvider
{
    /**
     * Register services in the container.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/ayon.php',
            'ayon'
        );

        $this->app->singleton(AyonClientInterface::class, AyonClient::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/ayon.php' => config_path('ayon.php'),
        ], 'ayon-config');
    }
}
