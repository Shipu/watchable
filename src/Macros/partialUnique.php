<?php

use Illuminate\Database\Schema\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Create a unique index limited to rows matching a predicate (e.g. soft-delete-aware
 * uniqueness: `deleted_at is null`). Laravel's fluent schema builder has no native way
 * to express this — see https://github.com/laravel/framework/pull/61007, closed unmerged.
 *
 * Supported on pgsql and sqlite only; both compile the same `where` syntax. mysql and
 * sqlsrv don't support this the same way, so this throws rather than emit wrong SQL.
 *
 * @param  string  $table
 * @param  string|array<int, string>  $columns
 * @param  string  $indexName
 * @param  string  $whereRaw  Raw SQL predicate, e.g. "deleted_at is null". Not parameterized —
 *                            callers must not interpolate untrusted input into this string.
 * @return void
 */
Builder::macro('partialUnique', function (string $table, string|array $columns, string $indexName, string $whereRaw) {
    /** @var \Illuminate\Database\Schema\Builder $this */
    $connection = $this->getConnection();
    $driver = $connection->getDriverName();

    if (! in_array($driver, ['pgsql', 'sqlite'], true)) {
        throw new \RuntimeException(
            "partialUnique() is not supported on the [{$driver}] driver — only pgsql and sqlite support partial indexes."
        );
    }

    $grammar = $connection->getSchemaGrammar();

    $columnList = collect((array) $columns)
        ->map(fn (string $column) => $grammar->wrap($column))
        ->implode(', ');

    $wrappedTable = $grammar->wrapTable($table);
    $wrappedIndex = $grammar->wrap($indexName);

    DB::statement("create unique index {$wrappedIndex} on {$wrappedTable} ({$columnList}) where {$whereRaw}");
});

/**
 * Drop a partial unique index created via partialUnique(). A plain `dropUnique()` /
 * `dropIndex()` on the Blueprint won't find it on pgsql once it's a genuine partial
 * index rather than a unique constraint, so this is the matching counterpart.
 *
 * @param  string  $indexName
 * @return void
 */
Builder::macro('dropPartialUnique', function (string $indexName) {
    /** @var \Illuminate\Database\Schema\Builder $this */
    $grammar = $this->getConnection()->getSchemaGrammar();

    DB::statement('drop index '.$grammar->wrap($indexName));
});
