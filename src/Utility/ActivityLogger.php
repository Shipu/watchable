<?php

namespace Shipu\Watchable\Utility;

use Shipu\Watchable\Models\Activity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;

class ActivityLogger
{

    protected $enableLog;

    protected $event;

    protected $model;

    protected $data;

    protected $remarks;

    public function on(Model $model)
    {
        $this->model($model);

        return $this;
    }

    public function model(Model $model)
    {
        $this->model = $model;

        return $this;
    }

    public function data($data)
    {
        $this->data = $data;

        return $this;
    }

    public function event($event)
    {
        $this->event = $event;

        return $this;
    }

    public function remarks($remarks)
    {
        $this->remarks = $remarks;

        return $this;
    }

    public function log($strings = null)
    {
        $activity             = app(Activity::class);
        $activity->model_type = get_class($this->model);
        $activity->model_id   = $this->model->id;
        $activity->data       = $this->data;
        $activity->remarks    = blank($this->remarks) ? $strings : $this->remarks;
        $activity->event      = $this->event;

        try {
            $activity->save();

            return $activity;
        } catch (QueryException $e) {
            return isset($e->errorInfo[2]) ? $e->errorInfo[2] : $e->getMessage();
        }
    }
}