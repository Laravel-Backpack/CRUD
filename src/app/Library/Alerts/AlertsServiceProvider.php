<?php

namespace Backpack\CRUD\app\Library\Alerts;

use Illuminate\Support\ServiceProvider;

class AlertsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../../../../config/backpack/alerts.php' => config_path('backpack/alerts.php'),
        ], 'backpack-alerts-config');
    }

    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../../../config/backpack/alerts.php',
            'backpack.alerts',
        );

        $this->app->singleton('alerts', function ($app) {
            return new AlertsMessageBag(
                $app['session.store'],
                $app['config']->get('backpack.alerts.session_key', 'alert_messages'),
            );
        });
    }

    public function provides(): array
    {
        return ['alerts'];
    }
}
