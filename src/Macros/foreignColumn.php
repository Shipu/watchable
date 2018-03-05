<?php

use Illuminate\Database\Schema\Blueprint;

/**
 * Add tree column for Nested Set.
 *
 * @return void
 */
Blueprint::macro('foreignColumn', function ($dataType, $localColumnName, $foreignTable, $foreignPrimaryColumn = 'id', $nullable = false, $index = true, $cascades = null) {
    $column = $this->{$dataType}($localColumnName);
    if ($nullable) {
        $column->nullable();
    }
    if ($index) {
        $column->index();
    }

    $foreignKey = $this->foreign($localColumnName)
        ->references($foreignPrimaryColumn)
        ->on($foreignTable);

    if (!empty($cascades)) {
        foreach ($cascades as $cascadeType => $action) {
            if (!empty($action)) {
                $foreignKey->{$cascadeType}($action);
            }
        }
    }

    return $column;
});