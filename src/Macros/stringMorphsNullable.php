<?php

use Illuminate\Database\Schema\Blueprint;

/**
 * Add nullable audit columns for a polymorphic table.
 *
 * @param bool $nullable
 * @return void
 */
Blueprint::macro('stringMorphsNullable', function ($name, $indexName = null) {
    $this->string("{$name}_id")->nullable();
    $this->string("{$name}_type")->nullable();
    $this->index(["{$name}_id", "{$name}_type"], $indexName);
});