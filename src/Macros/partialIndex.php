<?php

use Illuminate\Database\Schema\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Create a (non-unique) index limited to rows matching a predicate.
 * Sibling to partialUnique() — see that file for the driver-support note.
 *
 * @param  string  $table
 * @param  string|array<int, string>  $columns
 * @param  string  $indexName
 * @param  string  $whereRaw  Raw SQL predicate, e.g. "is_active". Not parameterized —
 *                            callers must not interpolate untrusted input into this string.
 * @return void
 */
Builder::macro('partialIndex', function (string $table, string|array $columns, string $indexName, string $whereRaw) {
    /** @var \Illuminate\Database\Schema\Builder $this */
    $connection = $this->getConnection();
    $driver = $connection->getDriverName();

    if (! in_array($driver, ['pgsql', 'sqlite'], true)) {
        throw new \RuntimeException(
            "partialIndex() is not supported on the [{$driver}] driver — only pgsql and sqlite support partial indexes."
        );
    }

    $grammar = $connection->getSchemaGrammar();

    $columnList = collect((array) $columns)
        ->map(fn (string $column) => $grammar->wrap($column))
        ->implode(', ');

    $wrappedTable = $grammar->wrapTable($table);
    $wrappedIndex = $grammar->wrap($indexName);

    DB::statement("create index {$wrappedIndex} on {$wrappedTable} ({$columnList}) where {$whereRaw}");
});

/**
 * Drop a partial index created via partialIndex().
 *
 * @param  string  $indexName
 * @return void
 */
Builder::macro('dropPartialIndex', function (string $indexName) {
    /** @var \Illuminate\Database\Schema\Builder $this */
    $grammar = $this->getConnection()->getSchemaGrammar();

    DB::statement('drop index '.$grammar->wrap($indexName));
});
