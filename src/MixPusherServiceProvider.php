<?php

namespace Eolme\MixPusher;

use Illuminate\Support\ServiceProvider;

class MixPusherServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/mix-pusher.php' => config_path('mix-pusher.php'),
        ]);

        //Register commands
        $this->commands([MixPusherCacheCommand::class]);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/mix-pusher.php', 'mix-pusher');

        $this->app->singleton('command.mix-pusher.cache', function () {
            return new MixPusherCacheCommand();
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            'command.mix-pusher.cache',
        ];
    }
}
