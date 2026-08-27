<?php

namespace Shipu\Watchable\Schema\Grammars;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Grammars\SQLiteGrammar;
use Illuminate\Support\Fluent;
use Shipu\Watchable\Support\PartialIndexWhere;

/**
 * Unlike Postgres, SQLite's native compileUnique() is already a plain
 * `create unique index ... on ... (...)` statement (no ALTER TABLE ADD CONSTRAINT
 * form), so appending ` where ...` to the parent's output is safe here — no need to
 * regenerate the whole statement the way PostgresPartialIndexGrammar does.
 */
class SQLitePartialIndexGrammar extends SQLiteGrammar
{
    public function compileUnique(Blueprint $blueprint, Fluent $command)
    {
        $sql = parent::compileUnique($blueprint, $command);

        if (is_null($command->where)) {
            return $sql;
        }

        return $sql.' where '.PartialIndexWhere::resolve($this->connection, $command->where);
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
