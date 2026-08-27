<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Grammars\Grammar;
use Illuminate\Database\Schema\Grammars\PostgresGrammar;
use Illuminate\Database\Schema\Grammars\SQLiteGrammar;
use Illuminate\Support\Fluent;
use Shipu\Watchable\Support\PartialIndexWhere;

/**
 * Fluent, in-the-closure partial (non-unique) index. Sibling to partialUnique() — see
 * that file for the full explanation of the two-macro mechanism and the three ways to
 * supply the predicate (raw string, chained ->where(string), chained ->where(closure)).
 *
 * @param  string|array<int, string>  $columns
 * @param  string  $indexName
 * @param  string|Closure(\Illuminate\Database\Query\Builder): mixed|null  $where
 * @return \Illuminate\Support\Fluent
 */
Blueprint::macro('partialIndex', function (string|array $columns, string $indexName, string|Closure|null $where = null) {
    /** @var \Illuminate\Database\Schema\Blueprint $this */
    return $this->addCommand('partialIndex', [
        'index' => $indexName,
        'columns' => (array) $columns,
        'where' => $where,
    ]);
});

Grammar::macro('compilePartialIndex', function (Blueprint $blueprint, Fluent $command) {
    /** @var \Illuminate\Database\Schema\Grammars\Grammar $this */
    if (! ($this instanceof PostgresGrammar || $this instanceof SQLiteGrammar)) {
        throw new RuntimeException(sprintf(
            'partialIndex() is not supported on the [%s] driver — only pgsql and sqlite support partial indexes.',
            class_basename($this)
        ));
    }

    if (is_null($command->where)) {
        throw new RuntimeException('partialIndex() needs a predicate — pass it as the 3rd argument or chain ->where(...).');
    }

    $whereSql = PartialIndexWhere::resolve($this->connection, $command->where);

    return sprintf('create index %s on %s (%s) where %s',
        $this->wrap($command->index),
        $this->wrapTable($blueprint),
        $this->columnize($command->columns),
        $whereSql
    );
});
