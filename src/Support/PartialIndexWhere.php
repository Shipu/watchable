<?php

namespace Shipu\Watchable\Support;

use Closure;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * Compiles a partial-index predicate — a raw string, or a closure building it via a
 * real Illuminate\Database\Query\Builder — into inlined SQL suitable for a DDL
 * statement (CREATE INDEX can't take bound placeholders the way DML can).
 *
 * Binding inlining reuses Laravel's own Grammar::substituteBindingsIntoRawSql(), the
 * same method backing Builder::toRawSql() — it already handles quote-escaping and the
 * `??`-for-literal-`?` operator correctly, which a naive str_replace/preg_replace
 * would not.
 */
class PartialIndexWhere
{
    public static function resolve(Connection $connection, string|Closure $where): string
    {
        if (is_string($where)) {
            return $where;
        }

        $query = new QueryBuilder($connection, $connection->getQueryGrammar(), $connection->getPostProcessor());

        $where($query);

        $grammar = $query->getGrammar();

        $sql = preg_replace('/^where\s+/i', '', $grammar->compileWheres($query));

        return $grammar->substituteBindingsIntoRawSql(
            $sql,
            $connection->prepareBindings($query->getBindings())
        );
    }
}
