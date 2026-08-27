<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Grammars\Grammar;
use Illuminate\Support\Fluent;

/**
 * Fluent CHECK constraints — Laravel's schema builder has no native support for these
 * at all (no `$table->check()`, nothing in Column Modifiers, nothing in the fluent
 * index list). Unlike partial indexes, CHECK is standard, portable SQL — every driver
 * Laravel supports understands `ADD CONSTRAINT ... CHECK (...)` — so this doesn't need
 * partialUnique()'s driver restriction, and definitely doesn't need the Blueprint/
 * Connection-swap machinery ->unique()->where(...) needed: `check` isn't an existing
 * Blueprint method, so there's nothing to fight over method_exists() precedence with.
 * A plain macro pair is enough.
 *
 * Usage — inside Schema::create()/Schema::table():
 *
 *   $table->check('total = subtotal - discount_amount AND total >= 0', 'chk_total');
 *
 * The name is required (not auto-generated) so it matches whatever your schema
 * documentation/reference already calls it — auto-generated names are exactly the kind
 * of drift this package's other features (partialUnique, ->unique()->where()) already
 * go out of their way to avoid.
 *
 * @param  string  $expression  Raw SQL boolean expression. Not parameterized — build it
 *                               from fixed strings in your migration, never from user input.
 * @param  string  $name
 * @return \Illuminate\Support\Fluent
 */
Blueprint::macro('check', function (string $expression, string $name) {
    /** @var \Illuminate\Database\Schema\Blueprint $this */
    return $this->addCommand('check', [
        'expression' => $expression,
        'index' => $name,
    ]);
});

Grammar::macro('compileCheck', function (Blueprint $blueprint, Fluent $command) {
    /** @var \Illuminate\Database\Schema\Grammars\Grammar $this */
    return sprintf('alter table %s add constraint %s check (%s)',
        $this->wrapTable($blueprint),
        $this->wrap($command->index),
        $command->expression
    );
});

/**
 * Native dropUnique()/dropIndex() both assume they're dropping an index, not a table
 * constraint — on Postgres those compile to `drop index`, which fails against a CHECK
 * constraint (`ERROR: "chk_total" is not an index`). This is the matching counterpart.
 *
 * @param  string  $name
 * @return \Illuminate\Support\Fluent
 */
Blueprint::macro('dropCheck', function (string $name) {
    /** @var \Illuminate\Database\Schema\Blueprint $this */
    return $this->addCommand('dropCheck', ['index' => $name]);
});

Grammar::macro('compileDropCheck', function (Blueprint $blueprint, Fluent $command) {
    /** @var \Illuminate\Database\Schema\Grammars\Grammar $this */
    return sprintf('alter table %s drop constraint %s',
        $this->wrapTable($blueprint),
        $this->wrap($command->index)
    );
});
