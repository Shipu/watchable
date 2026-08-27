<?php

namespace Shipu\Watchable\Schema;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ColumnDefinition;

/**
 * Stock ColumnDefinition is a bare Fluent attribute bag with no reference back to the
 * Blueprint that created it — $table->string('slug')->unique() just sets a `unique`
 * flag, silently promoted into a real index command later by Blueprint's internal
 * addFluentIndexes(), long after the closure has finished. That timing makes it
 * impossible to chain ->where(...) onto that later-created command.
 *
 * This subclass gets a real Blueprint reference and overrides unique()/index() to
 * queue the real command immediately (bypassing the flag/promotion mechanism
 * entirely for these two), so the IndexDefinition it returns is the actual queued
 * command — the same one ->where(...) already knows how to attach to, since it's a
 * plain Fluent object and Fluent's magic __call() stores unknown method calls as
 * attributes for free.
 */
class PartialIndexColumnDefinition extends ColumnDefinition
{
    protected ?Blueprint $blueprint = null;

    public function setBlueprint(Blueprint $blueprint): static
    {
        $this->blueprint = $blueprint;

        return $this;
    }

    /**
     * @param  string|null  $indexName
     * @return \Illuminate\Support\Fluent
     */
    public function unique($indexName = null)
    {
        return $this->blueprint->unique($this->name, $indexName);
    }

    /**
     * @param  string|null  $indexName
     * @return \Illuminate\Support\Fluent
     */
    public function index($indexName = null)
    {
        return $this->blueprint->index($this->name, $indexName);
    }
}
