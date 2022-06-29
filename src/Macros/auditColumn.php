<?php

use Illuminate\Database\Schema\Blueprint;

/**
 * Add the proper audit columns for a polymorphic table.
 *
 * @param bool $nullable
 * @return void
 */
Blueprint::macro('auditColumn', function ($nullable = false, $polymorphicColumnTypeString = false) {
    if(!$nullable) {
        if($polymorphicColumnTypeString) {
            $this->string("{$name}_id");
            $this->string("{$name}_type");
            $this->index(["{$name}_id", "{$name}_type"]);
        } else {
            $this->morphs(config('watchable.audit_columns.creator_column'));
            $this->morphs(config('watchable.audit_columns.editor_column'));
        }
    } else {
        if($polymorphicColumnTypeString) {
            $this->string("{$name}_id")->nullable();
            $this->string("{$name}_type")->nullable();
            $this->index(["{$name}_id", "{$name}_type"]);
        } else {
            $this->nullableAuditColumn();
        }
    }
});