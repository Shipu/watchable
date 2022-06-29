<?php

use Illuminate\Database\Schema\Blueprint;

/**
 * Add nullable audit columns for a polymorphic table.
 *
 * @param bool $nullable
 * @return void
 */
Blueprint::macro('stringMorphs', function ($name, $indexName = null) {
    $this->string("{$name}_id");
    $this->string("{$name}_type");
    $this->index(["{$name}_id", "{$name}_type"], $indexName);
});