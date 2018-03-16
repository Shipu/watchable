<?php

namespace Shipu\Watchable\Traits;

trait DynamicAttributes
{
    public function __get($key)
    {
        if (method_exists($this, 'watchAttribute')) {
            $this->watchAttribute();
        }
        return parent::__get($key);
    }
}