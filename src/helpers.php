<?php

use Shipu\Watchable\Utility\ActivityLogger;

if(!function_exists('activity')) {
    function activity() {
        return app(ActivityLogger::class);
    }
}
