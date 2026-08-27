<?php

namespace Shipu\Watchable\Schema;

use Illuminate\Database\Schema\Blueprint;

/**
 * Only override is addColumn(): build a PartialIndexColumnDefinition instead of the
 * stock ColumnDefinition, and hand it a reference to this Blueprint so its unique()/
 * index() overrides can queue a real command immediately. Everything else is
 * untouched — every other column type/modifier behaves exactly like stock Laravel.
 *
 * Installed via Schema::blueprintResolver() in WatchableServiceProvider, scoped to
 * connections resolved through PostgresPartialIndexConnection/SQLitePartialIndexConnection
 * so it never affects mysql/sqlsrv connections.
 */
class PartialIndexBlueprint extends Blueprint
{
    /**
     * @param  string  $type
     * @param  string  $name
     * @param  array<string, mixed>  $parameters
     * @return \Illuminate\Database\Schema\ColumnDefinition
     */
    public function addColumn($type, $name, array $parameters = [])
    {
        $definition = new PartialIndexColumnDefinition(array_merge(
            ['type' => $type, 'name' => $name],
            $parameters
        ));

        $definition->setBlueprint($this);

        return $this->addColumnDefinition($definition);
    }
}
