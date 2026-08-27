<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Grammars\Grammar;
use Illuminate\Database\Schema\Grammars\PostgresGrammar;
use Illuminate\Database\Schema\Grammars\SQLiteGrammar;
use Illuminate\Support\Fluent;

/**
 * Fluent, in-the-closure partial unique index — an index limited to rows matching a
 * predicate (e.g. soft-delete-aware uniqueness: `deleted_at is null`). Laravel's schema
 * builder has no native way to express this — see
 * https://github.com/laravel/framework/pull/61007, closed unmerged (Taylor Otwell:
 * "consider releasing your code as a package").
 *
 * Queues a command the same way $table->unique()/index() do; the matching
 * compilePartialUnique() grammar macro (registered below) turns it into SQL.
 *
 * Usage:
 *   Schema::create('products', function (Blueprint $table) {
 *       $table->string('slug');
 *       $table->softDeletes();
 *       $table->partialUnique('slug', 'uniq_products_slug', 'deleted_at is null');
 *   });
 *
 * @param  string|array<int, string>  $columns
 * @param  string  $indexName
 * @param  string  $whereRaw  Raw SQL predicate. Not parameterized — build it from fixed
 *                            strings in your migration, never from user input.
 * @return \Illuminate\Support\Fluent
 */
Blueprint::macro('partialUnique', function (string|array $columns, string $indexName, string $whereRaw) {
    /** @var \Illuminate\Database\Schema\Blueprint $this */
    return $this->addCommand('partialUnique', [
        'index' => $indexName,
        'columns' => (array) $columns,
        'where' => $whereRaw,
    ]);
});

/**
 * Grammar macros are stored on one shared array (only the base Grammar class uses the
 * Macroable trait — subclasses don't redeclare it), so registering this once makes it
 * available on every driver's grammar. The instanceof check below is what actually
 * limits support to pgsql/sqlite; it throws for everything else rather than emit wrong
 * SQL. This is why it's registered on the base Grammar class, not PostgresGrammar.
 */
Grammar::macro('compilePartialUnique', function (Blueprint $blueprint, Fluent $command) {
    /** @var \Illuminate\Database\Schema\Grammars\Grammar $this */
    if (! ($this instanceof PostgresGrammar || $this instanceof SQLiteGrammar)) {
        throw new \RuntimeException(sprintf(
            'partialUnique() is not supported on the [%s] driver — only pgsql and sqlite support partial indexes.',
            class_basename($this)
        ));
    }

    return sprintf('create unique index %s on %s (%s) where %s',
        $this->wrap($command->index),
        $this->wrapTable($blueprint),
        $this->columnize($command->columns),
        $command->where
    );
});
