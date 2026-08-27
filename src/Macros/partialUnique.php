<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Grammars\Grammar;
use Illuminate\Database\Schema\Grammars\PostgresGrammar;
use Illuminate\Database\Schema\Grammars\SQLiteGrammar;
use Illuminate\Support\Fluent;
use Shipu\Watchable\Support\PartialIndexWhere;

/**
 * Fluent, in-the-closure partial unique index — an index limited to rows matching a
 * predicate (e.g. soft-delete-aware uniqueness: `deleted_at is null`). Laravel's schema
 * builder has no native way to express this — see
 * https://github.com/laravel/framework/pull/61007, closed unmerged (Taylor Otwell:
 * "consider releasing your code as a package").
 *
 * Three ways to supply the predicate — pick whichever reads best:
 *
 *   $table->partialUnique('slug', 'uniq_products_slug', 'deleted_at is null');
 *   $table->partialUnique('slug', 'uniq_products_slug')->where('deleted_at is null');
 *   $table->partialUnique('slug', 'uniq_products_slug')->where(fn ($q) => $q->whereNull('deleted_at'));
 *
 * The closure form receives a real Illuminate\Database\Query\Builder — build the
 * predicate with normal where()/whereNull()/whereIn()/etc. Bindings are compiled by the
 * connection's own query grammar, then inlined via Laravel's own
 * Grammar::substituteBindingsIntoRawSql() (the same method behind Builder::toRawSql()) —
 * safe for values from user input, unlike the raw-string form.
 *
 * addCommand() returns a plain Fluent, and Fluent doesn't declare a `where()` method of
 * its own — the chained ->where(...) above lands on Fluent's magic __call(), which just
 * stores it as the `where` attribute. No extra macro needed for the chained form; it's
 * read on the same $command->where property this file's 3-argument form sets directly.
 *
 * @param  string|array<int, string>  $columns
 * @param  string  $indexName
 * @param  string|Closure(\Illuminate\Database\Query\Builder): mixed|null  $where  Optional —
 *         may be supplied here or via ->where() chained onto the returned command.
 * @return \Illuminate\Support\Fluent
 */
Blueprint::macro('partialUnique', function (string|array $columns, string $indexName, string|Closure|null $where = null) {
    /** @var \Illuminate\Database\Schema\Blueprint $this */
    return $this->addCommand('partialUnique', [
        'index' => $indexName,
        'columns' => (array) $columns,
        'where' => $where,
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
        throw new RuntimeException(sprintf(
            'partialUnique() is not supported on the [%s] driver — only pgsql and sqlite support partial indexes.',
            class_basename($this)
        ));
    }

    if (is_null($command->where)) {
        throw new RuntimeException('partialUnique() needs a predicate — pass it as the 3rd argument or chain ->where(...).');
    }

    $whereSql = PartialIndexWhere::resolve($this->connection, $command->where);

    return sprintf('create unique index %s on %s (%s) where %s',
        $this->wrap($command->index),
        $this->wrapTable($blueprint),
        $this->columnize($command->columns),
        $whereSql
    );
});
