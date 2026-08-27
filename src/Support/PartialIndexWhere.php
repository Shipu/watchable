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
 *
 * Deliberately does NOT call Connection::prepareBindings() first, unlike
 * Builder::toRawSql() does. That method converts booleans to (int) 1/0 — correct
 * preparation for a *real* PDO bindValue() call, but wrong here: once a boolean has
 * become an int, Grammar::escape() can no longer tell it apart from a real integer
 * binding, so a Postgres boolean column ends up compared against a literal `1`
 * instead of `true` — `boolean = integer` has no operator on Postgres, so the
 * generated CREATE INDEX statement fails outright. Preserving DateTimeInterface
 * formatting (the only other thing prepareBindings() does) without the boolean
 * conversion is enough for every predicate this class needs to support.
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

        $bindings = array_map(
            fn ($value) => $value instanceof \DateTimeInterface ? $value->format($grammar->getDateFormat()) : $value,
            $query->getBindings()
        );

        return $grammar->substituteBindingsIntoRawSql($sql, $bindings);
    }
}
