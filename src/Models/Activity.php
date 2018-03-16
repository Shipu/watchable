<?php

namespace Shipu\Watchable\Models;

use Illuminate\Database\Eloquent\Model;
use Shipu\Watchable\Traits\HasAuditColumn;
use Shipu\Watchable\Traits\HasModelAttributesEvents;
use Shipu\Watchable\Traits\HasModelEvents;
use Shipu\Watchable\Traits\WatchableTrait;

class Activity extends Model
{
    use HasModelAttributesEvents;

    protected $table = 'activity_logs';

    protected $casts = [
        'data' => 'collection'
    ];

    protected $guarded = [];

    public $auditColumn = true;

    public $activityLog = false;

    public function getModelAttribute()
    {
        return $this->model_type::find($this->model_id);
    }

    public function getChangesAttribute()
    {
        return collect($this->data['changes']);
    }

    public function getOldAttribute()
    {
        return collect($this->data['old']);
    }

    public function onDataAttributeSaving($newValue, $oldValue)
    {

    }
}
