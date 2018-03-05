<?php

use Illuminate\Database\Schema\Blueprint;

/**
 * Add the proper audit columns for a polymorphic table.
 *
 * @param bool $nullable
 * @return void
 */
Blueprint::macro('bigAuditColumn', function ($nullable = false) {
    if(!$nullable) {
        $this->bigMorphs(config('watchable.audit_columns.creator_column'));
        $this->bigMorphs(config('watchable.audit_columns.editor_column'));
    } else {
        $this->nullableBigAuditColumn();
    }
});