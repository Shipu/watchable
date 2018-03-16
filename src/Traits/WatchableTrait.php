<?php

namespace Shipu\Watchable\Traits;


trait WatchableTrait
{
    use HasAuditColumn, HasModelEvents, ActivityLogTrait, HasModelAttributesEvents, DynamicAttributes;
}