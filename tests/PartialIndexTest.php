<?php

use Illuminate\Database\Query\Expression;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Grammars\MySqlGrammar;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Fluent;

test('partialUnique, declared fluently in the closure, allows reuse after soft delete', function () {
    Schema::create('products', function (Blueprint $table) {
        $table->id();
        $table->string('slug');
        $table->timestamp('deleted_at')->nullable();

        $table->partialUnique('slug', 'uniq_products_slug', 'deleted_at is null');
    });

    DB::table('products')->insert(['slug' => 'bar-council', 'deleted_at' => now()]);
    DB::table('products')->insert(['slug' => 'bar-council', 'deleted_at' => null]);

    expect(DB::table('products')->count())->toBe(2);
});

test('partialUnique rejects a true duplicate among non-deleted rows', function () {
    Schema::create('products', function (Blueprint $table) {
        $table->id();
        $table->string('slug');
        $table->timestamp('deleted_at')->nullable();

        $table->partialUnique('slug', 'uniq_products_slug', 'deleted_at is null');
    });

    DB::table('products')->insert(['slug' => 'bar-council', 'deleted_at' => null]);

    expect(fn () => DB::table('products')->insert(['slug' => 'bar-council', 'deleted_at' => null]))
        ->toThrow(QueryException::class);
});

test('partialIndex, declared fluently in the closure, creates a working conditional index', function () {
    Schema::create('products', function (Blueprint $table) {
        $table->id();
        $table->string('slug');
        $table->boolean('is_active')->default(true);

        $table->partialIndex('slug', 'idx_active_products_slug', 'is_active');
    });

    DB::table('products')->insert(['slug' => 'a', 'is_active' => true]);
    DB::table('products')->insert(['slug' => 'a', 'is_active' => true]);

    expect(DB::table('products')->count())->toBe(2);
});

test('the native dropIndex() removes a partial index created via partialUnique', function () {
    Schema::create('products', function (Blueprint $table) {
        $table->id();
        $table->string('slug');
        $table->timestamp('deleted_at')->nullable();

        $table->partialUnique('slug', 'uniq_products_slug', 'deleted_at is null');
    });

    Schema::table('products', function (Blueprint $table) {
        $table->dropIndex('uniq_products_slug');
    });

    DB::table('products')->insert(['slug' => 'bar-council', 'deleted_at' => null]);
    DB::table('products')->insert(['slug' => 'bar-council', 'deleted_at' => null]);

    expect(DB::table('products')->count())->toBe(2);
});

test('the chained ->where(string) form behaves the same as the 3-argument form', function () {
    Schema::create('products', function (Blueprint $table) {
        $table->id();
        $table->string('slug');
        $table->timestamp('deleted_at')->nullable();

        $table->partialUnique('slug', 'uniq_products_slug')->where('deleted_at is null');
    });

    DB::table('products')->insert(['slug' => 'bar-council', 'deleted_at' => now()]);
    DB::table('products')->insert(['slug' => 'bar-council', 'deleted_at' => null]);

    expect(DB::table('products')->count())->toBe(2);
});

test('the chained ->where(closure) form builds the predicate with a real query builder', function () {
    Schema::create('products', function (Blueprint $table) {
        $table->id();
        $table->string('slug');
        $table->string('status');
        $table->timestamp('deleted_at')->nullable();

        $table->partialUnique('slug', 'uniq_products_slug')
            ->where(fn ($query) => $query->whereNull('deleted_at')->where('status', 'draft'));
    });

    // Different status — not covered by the partial index, so this duplicate is allowed.
    DB::table('products')->insert(['slug' => 'bar-council', 'status' => 'published', 'deleted_at' => null]);
    DB::table('products')->insert(['slug' => 'bar-council', 'status' => 'published', 'deleted_at' => null]);

    // Both draft and non-deleted — this is what the index actually protects.
    DB::table('products')->insert(['slug' => 'unique-slug', 'status' => 'draft', 'deleted_at' => null]);
    expect(fn () => DB::table('products')->insert(['slug' => 'unique-slug', 'status' => 'draft', 'deleted_at' => null]))
        ->toThrow(QueryException::class);
});

test('the closure form inlines bindings safely for a value containing a literal quote', function () {
    Schema::create('products', function (Blueprint $table) {
        $table->id();
        $table->string('slug');
        $table->string('name');

        $table->partialUnique('slug', 'uniq_products_slug')
            ->where(fn ($query) => $query->where('name', "O'Brien's Shop"));
    });

    DB::table('products')->insert(['slug' => 'a', 'name' => "O'Brien's Shop"]);

    expect(DB::table('products')->where('name', "O'Brien's Shop")->count())->toBe(1);
});

test('partialUnique() with no predicate creates a plain unique index, not a table constraint', function () {
    Schema::create('topics', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('parent_id')->nullable();
        $table->string('slug');

        $table->partialUnique([new Expression('COALESCE(parent_id, 0)'), 'slug'], 'uniq_topic_slug_per_parent');
    });

    DB::table('topics')->insert(['parent_id' => null, 'slug' => 'a']);
    DB::table('topics')->insert(['parent_id' => 1, 'slug' => 'a']);

    expect(fn () => DB::table('topics')->insert(['parent_id' => null, 'slug' => 'a']))
        ->toThrow(QueryException::class);
});

test('compilePartialUnique throws on an unsupported driver instead of emitting wrong SQL', function () {
    DB::connection()->useDefaultSchemaGrammar();

    $grammar = new MySqlGrammar(DB::connection());
    $blueprint = new Blueprint(DB::connection(), 'products');
    $command = new Fluent(['index' => 'uniq_products_slug', 'columns' => ['slug'], 'where' => 'deleted_at is null']);

    expect(fn () => $grammar->compilePartialUnique($blueprint, $command))
        ->toThrow(RuntimeException::class, 'not supported on the [MySqlGrammar] driver');
});

test('the closure form handles a boolean predicate value', function () {
    // Regression test for a real bug: Connection::prepareBindings() converts bool to
    // (int) before escaping, which is correct for a real PDO bindValue() call but
    // wrong for raw SQL inlining — Postgres has no `boolean = integer` operator, so
    // this failed outright against a real Postgres connection (sqlite's test
    // connection here can't reproduce that specific failure, since sqlite has no
    // strict boolean type and accepts `= 1` regardless; verified separately against
    // real Postgres).
    Schema::create('products', function (Blueprint $table) {
        $table->id();
        $table->string('slug');
        $table->boolean('is_active')->default(true);

        $table->partialIndex('slug', 'idx_active_slug')
            ->where(fn ($query) => $query->where('is_active', true));
    });

    DB::table('products')->insert(['slug' => 'a', 'is_active' => true]);
    DB::table('products')->insert(['slug' => 'a', 'is_active' => true]);

    expect(DB::table('products')->count())->toBe(2);
});
