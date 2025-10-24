<?php

namespace App\Providers;

use App\Services\Contracts\TalentAyonSyncServiceInterface;
use App\Services\TalentAyonSyncService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(TalentAyonSyncServiceInterface::class, TalentAyonSyncService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
    }
}
