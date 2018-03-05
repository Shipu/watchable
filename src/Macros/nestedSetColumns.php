<?php

use Illuminate\Database\Schema\Blueprint;

/**
 * Add tree column for Nested Set.
 *
 * @return void
 */
Blueprint::macro('nestedSetColumns', function () {
    $this->unsignedInteger('lft')->default(0);
    $this->unsignedInteger('rgt')->default(0);
    $this->unsignedInteger('depth')->default(0);
    $this->unsignedInteger('parent_id')->nullable();
    $this->index(['lft', 'rgt', 'parent_id']);
});