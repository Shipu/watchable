<?php

namespace Shipu\Watchable;

use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;

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
        //
    }
}
