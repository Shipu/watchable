<?php

use Illuminate\Database\Schema\Blueprint;

/**
 * Add nullable audit columns for a polymorphic table.
 *
 * @param bool $nullable
 * @return void
 */
Blueprint::macro('nullableAuditColumn', function ($polymorphicColumnTypeString = false) {
    if(!$polymorphicColumnTypeString) {
        $this->nullableMorphs(config('watchable.audit_columns.creator_column'));
        $this->nullableMorphs(config('watchable.audit_columns.editor_column'));
    } else {
        $this->stringMorphsNullable(config('watchable.audit_columns.creator_column'));
        $this->stringMorphsNullable(config('watchable.audit_columns.editor_column'));
    }
    
});