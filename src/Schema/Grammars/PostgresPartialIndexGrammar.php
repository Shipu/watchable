<?php

namespace Shipu\Watchable\Schema\Grammars;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Grammars\PostgresGrammar;
use Illuminate\Support\Fluent;
use Shipu\Watchable\Support\PartialIndexWhere;

/**
 * Native compileUnique() on Postgres compiles to `alter table ... add constraint ...
 * unique (...)` — a table CONSTRAINT, which Postgres syntax does not allow a WHERE
 * clause on at all. So when ->where(...) is present, this doesn't append to the
 * parent's SQL (that would be a syntax error) — it switches to `create unique index
 * ... where ...` instead, the only Postgres form that supports a partial predicate.
 * Falls through to parent::compileUnique() completely unchanged whenever no `where`
 * was chained on, so a plain $table->string('x')->unique() with no ->where() behaves
 * identically to stock Laravel.
 *
 * compileIndex() (non-unique) is already a plain `create index ... on ... (...)`
 * statement, so appending ` where ...` there is safe.
 */
class PostgresPartialIndexGrammar extends PostgresGrammar
{
    public function compileUnique(Blueprint $blueprint, Fluent $command)
    {
        if (is_null($command->where)) {
            return parent::compileUnique($blueprint, $command);
        }

        return sprintf('create unique index %s on %s (%s) where %s',
            $this->wrap($command->index),
            $this->wrapTable($blueprint),
            $this->columnize($command->columns),
            PartialIndexWhere::resolve($this->connection, $command->where)
        );
    }

    public function compileIndex(Blueprint $blueprint, Fluent $command)
    {
        $sql = parent::compileIndex($blueprint, $command);

        if (is_null($command->where)) {
            return $sql;
        }

        return $sql.' where '.PartialIndexWhere::resolve($this->connection, $command->where);
    }
}
