<?php

namespace Shipu\Watchable\Connections;

use Illuminate\Database\PostgresConnection;
use Illuminate\Database\Schema\Builder as SchemaBuilder;
use Shipu\Watchable\Schema\Grammars\PostgresPartialIndexGrammar;
use Shipu\Watchable\Schema\PartialIndexBlueprint;

/**
 * Registered via Connection::resolverFor('pgsql', ...) in WatchableServiceProvider —
 * every pgsql connection in the app is instantiated as this class instead of the
 * stock PostgresConnection. Everything is inherited unchanged except:
 *   - the schema grammar (so compileUnique/compileIndex know about ->where(...))
 *   - the schema builder's blueprint resolver (so $table->string(...)->unique()
 *     returns a real, immediately-queued command instead of a deferred flag)
 *
 * No other query/schema behavior differs from stock Laravel.
 */
class PostgresPartialIndexConnection extends PostgresConnection
{
    protected function getDefaultSchemaGrammar()
    {
        return new PostgresPartialIndexGrammar($this);
    }

    public function getSchemaBuilder()
    {
        if (is_null($this->schemaGrammar)) {
            $this->useDefaultSchemaGrammar();
        }

        $builder = new SchemaBuilder($this);

        $builder->blueprintResolver(fn ($connection, $table, $callback = null) => new PartialIndexBlueprint($connection, $table, $callback));

        return $builder;
    }
}
