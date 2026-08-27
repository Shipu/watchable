# Watchable
Watchable is a Laravel package where you can easily pick up laravel model event and activity log inside your application. 

```
use Shipu\Watchable\Traits\WatchableTrait;
```

```
onModelCreating
onModelCreated
onModelUpdating
onModelUpdated
```


The Package stores all activity in the `activity_logs` table.
Here's a demo of how you can use it:
```php
activity()->log('Look, I logged something');
```
You can retrieve all activity using the `Shipu\Watchable\Models\Activity` model.
```
Activity::all();
```
Here's a more advanced example:
```php
activity()
   ->on($anEloquentModel)
   ->data($storeWhatYouWantTo)
   ->log('Look, I logged something');
   
$lastLoggedActivity = Activity::all()->last();

$lastLoggedActivity->model; //returns an instance of an eloquent model
$lastLoggedActivity->causer; //returns an instance of your user model
$lastLoggedActivity->remarks; //returns 'Look, I logged something'
```
Here's an example on [event logging](https://docs.spatie.be/laravel-activitylog/v2/advanced-usage/logging-model-events).

```php
$user->name = 'updated name';
$user->save();

//updating the newsItem will cause the logging of an activity
$activity = Activity::all()->last();

$activity->remarks; //returns 'User Updated'
$activity->model; //returns the instance of NewsItem that was created
```

Calling `$activity->changes` will return this array:

```php
[
   'new' => [
        'name' => 'updated name',
        'text' => 'Lorum',
    ],
    'old' => [
        'name' => 'original name',
        'text' => 'Lorum',
    ],
];
```

## Installation

You can install the package via composer:
``` bash
composer require shipu/watchable
```
You can optionally publish the config file with:
```bash
php artisan vendor:publish --provider="Shipu\Watchable\WatchableServiceProvider" --tag="shipu-watchable-config"
```
This is the contents of the published config file:
```php
return [
    'audit_columns' => [
        'creator_column' => 'creator',
        'editor_column' => 'editor',
        'default_active' => false,
    ],
    'activity_log' => [
        'model' => \Shipu\Watchable\Models\Activity::class
    ]
];
```

You can publish the migration with:
```bash
php artisan vendor:publish --provider="Shipu\Watchable\WatchableServiceProvider" --tag="shipu-watchable-migrations"
```
*Note*: The default migration assumes you are using integers for your model IDs. If you are using UUIDs, or some other format, adjust the format of the subject_id and causer_id fields in the published migration before continuing.

After publishing the migration you can create the `activity_logs` table by running the migrations:
```bash
php artisan migrate
```

## Partial indexes

Laravel's schema builder has no fluent way to create a partial index (an index limited to rows
matching a `WHERE` predicate — e.g. a unique slug that's only enforced while `deleted_at is null`).
This has been proposed upstream three times and closed unmerged each time (see
[laravel/framework#61007](https://github.com/laravel/framework/pull/61007),
[#61097](https://github.com/laravel/framework/pull/61097),
[#61098](https://github.com/laravel/framework/pull/61098)) — Taylor Otwell's guidance was to ship it
as a package instead, so here it is.

Supported on **pgsql and sqlite only** (both compile the same `where` syntax). Throws a
`RuntimeException` on mysql/sqlsrv rather than emit incorrect SQL.

Declared fluently, inside the `Schema::create()` closure, right alongside the column it applies to.
Three ways to supply the predicate:

```php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

Schema::create('products', function (Blueprint $table) {
    $table->id();
    $table->string('slug');
    $table->string('status');
    $table->integer('position');
    $table->softDeletes();

    // 1. Raw string, 3rd argument.
    $table->partialUnique('slug', 'uniq_products_slug', 'deleted_at is null');

    // 2. Raw string, chained.
    $table->partialUnique('slug', 'uniq_products_slug')->where('deleted_at is null');

    // 3. Closure — receives a real Illuminate\Database\Query\Builder. Safe for values
    //    from user input; bindings are inlined via Laravel's own binding-substitution
    //    logic (the same code behind Builder::toRawSql()), not string concatenation.
    $table->partialUnique('slug', 'uniq_products_slug')
        ->where(fn ($query) => $query->whereNull('deleted_at'));

    // Non-unique conditional index — same three forms.
    $table->partialIndex(['status', 'position'], 'idx_products_listing')
        ->where(fn ($query) => $query->whereNull('deleted_at'));
});
```

Drop them the same way you'd drop any index — no special method needed, `dropIndex()` already
works for a partial index by name:

```php
Schema::table('products', function (Blueprint $table) {
    $table->dropIndex('uniq_products_slug');
});
```

The raw-string forms (1 and 2) are **not parameterized** — build them from fixed strings in your
migration, never from user input. Reach for the closure form (3) whenever a value in the predicate
could come from outside your codebase.

### How this works without touching Laravel core

This needs two macros, not one, because of how `Schema::create()` actually executes:

1. **`Blueprint::macro('partialUnique', ...)`** — called inside the closure, it just queues a
   command via `$this->addCommand(...)`, exactly like Laravel's own `$table->unique()` does. The
   `CREATE TABLE` statement is *also* just a queued command at this point (added first, before your
   closure runs) — nothing has hit the database yet.
2. **`Grammar::macro('compilePartialUnique', ...)`** — Laravel dispatches each queued command to SQL
   via `$grammar->{'compile'.ucfirst($command->name)}(...)`, checked with
   `method_exists(...) || $grammar::hasMacro(...)`. Registering a `compilePartialUnique` macro on the
   *base* `Illuminate\Database\Schema\Grammars\Grammar` class means it's picked up by that dispatch
   exactly like a real compiler method, for every driver — the driver subclasses don't redeclare the
   `Macroable` trait, so macro storage is shared across all of them (verified: registering a macro on
   `PostgresGrammar` makes `MySqlGrammar::hasMacro(...)` true too). That's *why* the compiler macro
   does its own `instanceof PostgresGrammar || instanceof SQLiteGrammar` check and throws otherwise,
   rather than being "not registered" for unsupported drivers.

Execution order is safe because `Schema::create()` always queues the `create` command before running
your closure (`$blueprint->create(); $callback($blueprint);`), and `Blueprint::build()` executes each
compiled statement in queue order — so by the time `compilePartialUnique`'s `CREATE INDEX` statement
runs, `CREATE TABLE` has already run on the connection.

A **Blueprint-only** macro can't do this on its own: even if it queued a command, Blueprint has no
way to teach `PostgresGrammar`/`SQLiteGrammar` a new `compilePartialUnique` method — that dispatch is
a hardcoded method lookup on the grammar class, which is exactly why the 3 rejected upstream PRs had
to edit `PostgresGrammar.php`/`SQLiteGrammar.php`/`SqlServerGrammar.php` directly instead of shipping
as a package. Grammar macros are the loophole that makes a package-only implementation possible.

### Why the chained `->where(...)` form needed no third macro

`addCommand()` returns a plain `Illuminate\Support\Fluent` — and `Fluent` doesn't declare a
`where()` method of its own, so `->where(...)` chained onto it lands on `Fluent`'s own magic
`__call()`, which just stores whatever was passed as the `where` attribute. That's the exact same
`$command->where` property the 3-argument form sets directly — no extra registration needed, it
falls out of how `Fluent` already works.

This is also why `$table->unique(...)->where(...)` (chaining onto Laravel's *own* `unique()`)
doesn't and can't work: `Blueprint::unique()` returns an `IndexDefinition`, whose command is named
`'unique'`, dispatching to the real `compileUnique` — a method that already exists on
`PostgresGrammar`/`SQLiteGrammar` and therefore can never be reached through `__call`/macro dispatch
(PHP resolves a real declared method before ever consulting magic methods). A `where` attribute set
on that command would be silently ignored by the native compiler. Reusing `unique()`'s own name was
never on the table without editing Laravel core; `partialUnique` had to be a distinct command with
its own compiler from the start.

The closure form (`->where(fn ($query) => ...)`) builds the predicate with a real, disconnected
`Illuminate\Database\Query\Builder`, compiles it via `compileWheres()`, then inlines the resulting
bindings with `Grammar::substituteBindingsIntoRawSql()` — the same method backing
`Builder::toRawSql()`. That's a real safety difference from the raw-string forms, not just a style
choice: naive string interpolation (or even a hand-rolled `str_replace('?', ...)`) breaks on a value
containing a literal quote or Postgres's `?`/`??` operators; `substituteBindingsIntoRawSql()` already
handles both correctly, so this reuses it instead of re-implementing it.