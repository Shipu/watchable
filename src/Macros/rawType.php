<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Grammars\Grammar;
use Illuminate\Support\Fluent;

/**
 * Fluent raw-type columns — for column types Laravel's Blueprint has no method for at
 * all: Postgres extension types (`ltree`, `citext`, ...), array types (`bigint[]`,
 * `text[]`, ...), or a bare unscaled `numeric` (Laravel's `decimal()` always emits
 * `numeric(total, places)`, never plain `numeric`). Laravel's own escape hatch for this
 * is `$table->addColumn($type, $name, $parameters)`, which is real Laravel API (not a
 * macro) — it already stores whatever `$parameters` you hand it on the resulting
 * `ColumnDefinition` and dispatches compilation to `type{Ucfirst($type)}()` on the
 * grammar via a dynamic method call. That dynamic call is what lets a macro answer it:
 * nothing named `typeRaw` exists on any grammar, so Macroable's `__call` fallback
 * catches it exactly the same way `compileCheck`/`compilePartialUnique` already do.
 *
 * `rawType()` itself is just sugar over `addColumn('raw', ...)` so call sites don't
 * need to know the 'raw'/'sqlType' plumbing:
 *
 *   $table->rawType('path', 'ltree');
 *   $table->rawType('topic_ids', 'bigint[]')->default('{}');
 *   $table->rawType('value_num', 'numeric')->nullable();
 *
 * Modifiers (->nullable(), ->default(), ->unique(), ...) all still work — they're
 * applied by addModifiers() the same way for every column type, regardless of how that
 * type's SQL was produced.
 *
 * @param  string  $sqlType  Raw SQL type, inserted verbatim. Not escaped — build it
 *                            from a fixed string in your migration, never user input.
 * @return \Illuminate\Database\Schema\ColumnDefinition
 */
Blueprint::macro('rawType', function (string $name, string $sqlType) {
    /** @var \Illuminate\Database\Schema\Blueprint $this */
    return $this->addColumn('raw', $name, ['sqlType' => $sqlType]);
});

Grammar::macro('typeRaw', function (Fluent $column) {
    return $column->sqlType;
});
