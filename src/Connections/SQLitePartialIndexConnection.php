<?php

namespace Shipu\Watchable\Connections;

use Illuminate\Database\Schema\SQLiteBuilder;
use Illuminate\Database\SQLiteConnection;
use Shipu\Watchable\Schema\Grammars\SQLitePartialIndexGrammar;
use Shipu\Watchable\Schema\PartialIndexBlueprint;

/**
 * sqlite counterpart to PostgresPartialIndexConnection — see that class for the full
 * explanation. Registered via Connection::resolverFor('sqlite', ...).
 */
class SQLitePartialIndexConnection extends SQLiteConnection
{
    protected function getDefaultSchemaGrammar()
    {
        return new SQLitePartialIndexGrammar($this);
    }

    public function getSchemaBuilder()
    {
        if (is_null($this->schemaGrammar)) {
            $this->useDefaultSchemaGrammar();
        }

        $builder = new SQLiteBuilder($this);

        $builder->blueprintResolver(fn ($connection, $table, $callback = null) => new PartialIndexBlueprint($connection, $table, $callback));

        return $builder;
    }
}
