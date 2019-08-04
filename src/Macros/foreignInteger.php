<?php

use Illuminate\Database\Schema\Blueprint;

/**
 * Add tree column for Nested Set.
 *
 * @return void
 */
Blueprint::macro('foreignInteger', function ($localColumnName, $foreignTable, $foreignPrimaryColumn = 'id', $nullable = false, $index = true, $cascades = null) {
    return $this->foreignColumn('unsignedInteger', $localColumnName, $foreignTable, $foreignPrimaryColumn, $nullable, $index, $cascades);
});
