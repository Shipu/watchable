<?php

use Illuminate\Database\Schema\Blueprint;

/**
 * Add tree column for Nested Set.
 *
 * @return void
 */
Blueprint::macro('foreignBigIntegerNullable', function ($localColumnName, $foreignTable, $foreignPrimaryColumn = 'id', $nullable = true, $index = true, $cascades = null) {
    return $this->foreignColumn('unsignedBigInteger', $localColumnName, $foreignTable, $foreignPrimaryColumn, $nullable, $index, $cascades);
});
