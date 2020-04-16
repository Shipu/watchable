<?php

namespace Shipu\Watchable\Traits;

use ReflectionClass;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;

trait HasModelEvents
{
    /**
     * Automatically boot with Model, and register Events handler.
     */
    protected static function bootHasModelEvents()
    {
        static::getModelEvents()->each(function ($eventName) {
            if (method_exists(static::class, $eventName)) {
                static::$eventName(function (Model $model) use ($eventName) {
                    $reflect = new ReflectionClass($model);
                    Event::dispatch(strtolower($reflect->getShortName()) . '.' . $eventName, $model);
                    $method = 'on' . Str::studly('model_' . $eventName);
                    if (method_exists($model, $method)) {
                        $model->$method();
                    }
                });
            }
        });
    }

    /**
     * Get the model events to record activity for.
     *
     * @return array
     */
    protected static function getModelEvents()
    {
        if (isset(static::$recordEvents)) {
            return collect(static::$recordEvents);
        }

        return collect((new static)->getObservableEvents());
    }
}
