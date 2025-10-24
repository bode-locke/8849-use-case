<?php

namespace Benjamin\AyonConnector\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Benjamin\AyonConnector\AyonConnectorServiceProvider;

abstract class TestCase extends OrchestraTestCase
{
    /**
     * Load package service provider.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            AyonConnectorServiceProvider::class,
        ];
    }

    /**
     * Setup environment for tests.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('ayon.api_key', 'fake-key');
        $app['config']->set('ayon.api_url', 'https://fake-api.test');
    }
}
