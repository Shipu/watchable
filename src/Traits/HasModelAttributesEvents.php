<?php

namespace Shipu\Watchable\Traits;

use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait HasModelAttributesEvents
{
    /**
     * Automatically boot with Model, and register Events handler.
     */
    protected static function bootHasModelAttributesEvents()
    {
        static::getAttributesModelEvents()->each(function ($eventName) {
            if (method_exists(static::class, $eventName)) {
                static::$eventName(function (Model $model) use ($eventName) {
                    $changedAttributes = $model->getDirty();
                    foreach ($changedAttributes as $attribute => $newValue) {
                        $oldValue = $model->getOriginal($attribute);
                        $newValue = $model->getAttribute($attribute);

                        $hookMethod = "on" . Str::studly($attribute . "_attribute_{$eventName}");
                        if (method_exists($model, $hookMethod)) {
                            $message = sprintf("[%s::%s]", get_class($model), $hookMethod);
                            Log::info($message, compact('newValue', 'oldValue', 'model'));
                            $model->$hookMethod($newValue, $oldValue);
                        }
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
    protected static function getAttributesModelEvents()
    {
        if (isset(static::$attributeEvents)) {
            return collect(static::$attributeEvents);
        }

        return collect([
            'saving',
            'saved'
        ]);
    }
}
