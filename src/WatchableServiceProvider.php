<?php

namespace Shipu\Watchable;

use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\Application as LaravelApplication;
use Laravel\Lumen\Application as LumenApplication;

class WatchableServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        require_once __DIR__.'/helpers.php';
        Collection::make(glob(__DIR__.'/Macros/*.php'))
            ->each(function ($macro) {
                require_once $macro;
            });
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->publishConfig();
    }

    /**
     * Publish config.
     */
    protected function publishConfig()
    {
        $source = realpath(__DIR__.'/../config/watchable.php');
        // Check if the application is a Laravel OR Lumen instance to properly merge the configuration file.
        if ($this->app instanceof LaravelApplication && $this->app->runningInConsole()) {
            $this->publishes([$source => config_path('watchable.php')], 'shipu-watchable-config');
        } elseif ($this->app instanceof LumenApplication) {
            $this->app->configure('watchable');
        }
        $this->mergeConfigFrom($source, 'watchable');
    }
}
