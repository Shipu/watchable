<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Grammars\Grammar;
use Illuminate\Database\Schema\Grammars\PostgresGrammar;
use Illuminate\Database\Schema\Grammars\SQLiteGrammar;
use Illuminate\Support\Fluent;

/**
 * Fluent, in-the-closure partial (non-unique) index. Sibling to partialUnique() — see
 * that file for the full explanation of why this needs both a Blueprint macro (to queue
 * the command) and a Grammar macro (to compile it) rather than one or the other.
 *
 * @param  string|array<int, string>  $columns
 * @param  string  $indexName
 * @param  string  $whereRaw  Raw SQL predicate. Not parameterized — build it from fixed
 *                            strings in your migration, never from user input.
 * @return \Illuminate\Support\Fluent
 */
Blueprint::macro('partialIndex', function (string|array $columns, string $indexName, string $whereRaw) {
    /** @var \Illuminate\Database\Schema\Blueprint $this */
    return $this->addCommand('partialIndex', [
        'index' => $indexName,
        'columns' => (array) $columns,
        'where' => $whereRaw,
    ]);
});

Grammar::macro('compilePartialIndex', function (Blueprint $blueprint, Fluent $command) {
    /** @var \Illuminate\Database\Schema\Grammars\Grammar $this */
    if (! ($this instanceof PostgresGrammar || $this instanceof SQLiteGrammar)) {
        throw new \RuntimeException(sprintf(
            'partialIndex() is not supported on the [%s] driver — only pgsql and sqlite support partial indexes.',
            class_basename($this)
        ));
    }

    return sprintf('create index %s on %s (%s) where %s',
        $this->wrap($command->index),
        $this->wrapTable($blueprint),
        $this->columnize($command->columns),
        $command->where
    );
});
