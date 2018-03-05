<?php

use Illuminate\Database\Schema\Blueprint;

/**
 * Add the proper audit columns for a polymorphic table.
 *
 * @param bool $nullable
 * @return void
 */
Blueprint::macro('auditColumn', function ($nullable = false) {
    if(!$nullable) {
        $this->morphs(config('watchable.audit_columns.creator_column'));
        $this->morphs(config('watchable.audit_columns.editor_column'));
    } else {
        $this->nullableAuditColumn();
    }
});