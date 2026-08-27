<?php

/**
 * IDE helper stub for shipu/watchable's partial-index support.
 *
 * This file is never `require`d anywhere and is not part of the package's autoload
 * map — it exists purely so PhpStorm (and other IDEs using the same convention) index
 * these `@method` docblocks against Laravel's own classes. This is the same technique
 * barryvdh/laravel-ide-helper uses for its generated `_ide_helper.php`: a stub
 * re-declaration of an existing class, in a file the IDE statically scans, contributes
 * its docblocks to that class without touching vendor/ or requiring an extra
 * dependency in the consuming app. Since it's never executed, there's no class
 * redeclaration risk against the real Illuminate classes loaded via Composer.
 *
 * @see https://github.com/barryvdh/laravel-ide-helper
 */

namespace Illuminate\Database\Schema {
    /**
     * @method \Illuminate\Database\Schema\ColumnDefinition partialUnique(string|array $columns, string $indexName, string|\Closure|null $where = null) See shipu/watchable's README "Declared as its own method" section.
     * @method \Illuminate\Database\Schema\ColumnDefinition partialIndex(string|array $columns, string $indexName, string|\Closure|null $where = null) See shipu/watchable's README "Declared as its own method" section.
     * @method \Illuminate\Support\Fluent check(string $expression, string $name) Add a CHECK constraint. See shipu/watchable's README "CHECK constraints" section.
     * @method \Illuminate\Support\Fluent dropCheck(string $name) Drop a CHECK constraint added via check().
     * @method \Illuminate\Database\Schema\ColumnDefinition rawType(string $name, string $sqlType) Add a column using an arbitrary raw SQL type. See shipu/watchable's README "Raw-type columns" section.
     */
    class Blueprint
    {
        //
    }

    /**
     * Only accurate when shipu/watchable's config('watchable.partial_indexes.enabled')
     * is true (the default) — see the package README's "Chained directly onto
     * Laravel's own unique()/index()" section for what this actually requires.
     *
     * @method $this where(string|\Closure(\Illuminate\Database\Query\Builder): mixed $where) Attach a partial-index predicate. Raw SQL string, or a closure building it with a real query builder (bindings inlined safely).
     */
    class ColumnDefinition
    {
        //
    }

    /**
     * @method $this where(string|\Closure(\Illuminate\Database\Query\Builder): mixed $where) Attach a partial-index predicate. Raw SQL string, or a closure building it with a real query builder (bindings inlined safely).
     */
    class IndexDefinition
    {
        //
    }
}
