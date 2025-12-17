<?php

namespace App\Providers;

use App\Repositories\UserRepository;
use App\Services\External\AuthorizationService;
use App\Services\External\AuthorizationServiceInterface;
use App\Services\External\NotificationService;
use App\Services\External\NotificationServiceInterface;
use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AuthorizationServiceInterface::class, function ($app) {
            return new AuthorizationService(
                new Client(),
                config('services.authorize.url', 'https://util.devi.tools/api/v2/authorize')
            );
        });

        $this->app->singleton(NotificationServiceInterface::class, function ($app) {
            return new NotificationService(
                new Client(),
                config('services.notify.url', 'https://util.devi.tools/api/v1/notify')
            );
        });

        $this->app->singleton(UserRepository::class);
    }

    public function boot(): void
    {
        //
    }
}

