<?php

namespace Shipu\Watchable\Models;

use Illuminate\Database\Eloquent\Model;
use Shipu\Watchable\Traits\HasAuditColumn;
use Shipu\Watchable\Traits\HasModelAttributes;
use Shipu\Watchable\Traits\HasModelEvents;
use Shipu\Watchable\Traits\WatchableTrait;

class Activity extends Model
{
    use HasModelAttributes;

    protected $table = 'activity_logs';

    protected $casts = [
        'data' => 'collection'
    ];

    protected $guarded = [];

    public $auditColumn = true;

    public $activityLog = false;

//    public function getChangesAttribute()
//    {
//        return collect($this->data['dirty']);
//    }
//
//    public function getOldAttribute()
//    {
//        return collect($this->data['old']);
//    }

    public function onDataAttributeSaving($newValue, $oldValue)
    {
        dump($newValue);
        dd($oldValue);
    }
}
