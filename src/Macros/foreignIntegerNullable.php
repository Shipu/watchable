<?php

use Illuminate\Database\Schema\Blueprint;

/**
 * Add tree column for Nested Set.
 *
 * @return void
 */
Blueprint::macro('foreignIntegerNullable', function ($localColumnName, $foreignTable, $foreignPrimaryColumn = 'id', $nullable = true, $index = true, $cascades = null) {
    return $this->foreignColumn('unsignedInteger', $localColumnName, $foreignTable, $foreignPrimaryColumn, $nullable, $index, $cascades);
});
