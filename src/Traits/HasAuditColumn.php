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
        if (auth()->guest()) {
            return;
        }

        static::getHasAuditColumnModelEvents()->each(function ($evenName) {
            static::$evenName(function (Model $model) use ($evenName) {
                if ($model->defaultAuditColumn() || (!blank($model->auditColumn) && $model->auditColumn)) {
                    if ($evenName == 'creating') {
                        $model->setAuditColumn(config('watchable.audit_columns.creator_column'));
                    }
                    $model->setAuditColumn(config('watchable.audit_columns.editor_column'));
                }
            });
        });
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

    public function setAuditColumn($attribute)
    {
        if (auth()->check()) {
            $this->{"{$attribute}_id"}   = auth()->user()->id;
            $this->{"{$attribute}_type"} = get_class(auth()->user());
        }
    }

    public function defaultAuditColumn()
    {
        return config('watchable.audit_columns.default_active');
    }
}
