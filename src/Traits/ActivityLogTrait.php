<?php

namespace Shipu\Watchable\Traits;

use Shipu\Watchable\Utility\ActivityLogger;
use Illuminate\Database\Eloquent\Model;

trait ActivityLogTrait
{
    protected static function bootActivityLogTrait()
    {
        static::getActivityModelEvents()->each(function ($eventName) {
            if (method_exists(static::class, $eventName)) {
                static::$eventName(function (Model $model) use ($eventName) {
                    if (is_null($model->activityLog) || $model->activityLog) {
                        $data            = [];
                        $data['old']     = $model->getOriginal();
                        $data['new']     = $model->toArray();
                        $data['changes'] = $model->getDirty();
                        $data['deleted'] = [];

                        if ($eventName == 'deleted') {
                            $data['deleted'] = $model->getOriginal();
                        }
                        app(ActivityLogger::class)->event($eventName)
                            ->on($model)
                            ->data($data)
                            ->log(class_basename($model) . ' ' . ucfirst($eventName));
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
    protected static function getActivityModelEvents()
    {
        if (isset(static::$activityEvents)) {
            return static::$activityEvents;
        }

        return collect([
            'created',
            'updated',
            'deleted',
        ]);
    }

    public function getOriginalWithoutHidden()
    {
        $model = $this->getOriginal();
        collect($this->getHidden())->each(function ($attribute) use (&$model) {
            unset($model[ $attribute ]);
        });

        return $model;
    }
}