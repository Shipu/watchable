<?php

use Illuminate\Database\Schema\Blueprint;

/**
 * Add nullable audit columns for a polymorphic table.
 *
 * @param bool $nullable
 * @return void
 */
Blueprint::macro('nullableBigAuditColumn', function () {
    $this->bigMorphsNullable(config('watchable.audit_columns.creator_column'));
    $this->bigMorphsNullable(config('watchable.audit_columns.editor_column'));
});