<?php

namespace Shipu\Watchable\Traits;

use Illuminate\Database\Eloquent\Model;

trait HasAuditColumn
{
    /**
     * Automatically boot with Model, and register Events handler.
     */
    protected static function bootHasAuditColumn()
    {
        $auth = auth()->guard(static::getAuditColumnsConfig('guard'));

        if ($auth->guest()) {
            return;
        }

        static::getHasAuditColumnModelEvents()->each(function ($evenName) use ($auth) {
            static::$evenName(function (Model $model) use ($evenName, $auth) {
                if ($model->getAuditColumnsConfig('default_active') || (!blank($model->auditColumn) && $model->auditColumn)) {
                    if ($evenName == 'creating') {
                        $model->setAuditColumn($auth, $model->getAuditColumnsConfig('creator_column'));
                    }
                    $model->setAuditColumn($auth, $model->getAuditColumnsConfig('editor_column'));
                }
            });
        });
    }

    /**
     * Watchable config value
     *
     * @return array|string
     */
    protected static function getAuditColumnsConfig($key = null)
    {
        if (! is_null($key)) {
            return config('watchable.audit_columns.' . $key);
        }

        return config('watchable.audit_columns');
    }

    /**
     * Get the model events to record activity for.
     *
     * @return array
     */
    protected static function getHasAuditColumnModelEvents()
    {
        if (isset(static::$auditColumnEvents)) {
            return static::$auditColumnEvents;
        }

        return collect([
            'creating',
            'updating',
            'deleting',
        ]);
    }

    public function setAuditColumn($auth, $attribute)
    {
        if ($auth->check()) {
            $this->{"{$attribute}_id"}   = $auth->user()->id;
            $this->{"{$attribute}_type"} = get_class($auth->user());
        }
    }
}
