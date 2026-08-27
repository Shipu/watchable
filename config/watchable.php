<?php

return [
    'audit_columns' => [
        'creator_column' => 'creator',
        'editor_column' => 'editor',
        'default_active' => false,
    ],
    'activity_log' => [
        'model' => \Shipu\Watchable\Models\Activity::class
    ],

    /*
     * Enables $table->string('slug')->unique()->where(...) / ->index()->where(...) —
     * a chained partial-index predicate directly on Laravel's own unique()/index().
     *
     * This works by swapping every pgsql/sqlite Connection instance in the app for a
     * subclass (via Connection::resolverFor()) that uses a custom schema grammar and
     * blueprint resolver. It's a wider change than a typical package macro — every
     * query on those connections now runs through a class this package owns, not
     * Laravel's own PostgresConnection/SQLiteConnection — so it's flagged here rather
     * than silently always-on. Behavior is unaffected for every other query/schema
     * operation; only compileUnique()/compileIndex() differ, and only when
     * ->where(...) was actually chained.
     *
     * mysql/sqlsrv connections are never touched either way.
     */
    'partial_indexes' => [
        'enabled' => true,
    ],
];
