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
            $this->stringMorphs(config('watchable.audit_columns.creator_column'));
            $this->stringMorphs(config('watchable.audit_columns.editor_column'));
        } else {
            $this->morphs(config('watchable.audit_columns.creator_column'));
            $this->morphs(config('watchable.audit_columns.editor_column'));
        }
    } else {
        if($polymorphicColumnTypeString) {
            $this->stringMorphs(config('watchable.audit_columns.creator_column'));
            $this->stringMorphs(config('watchable.audit_columns.editor_column'));
        } else {
            $this->nullableAuditColumn();
        }
    }
});