<?php

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

test('compilePartialUnique throws on an unsupported driver instead of emitting wrong SQL', function () {
    DB::connection()->useDefaultSchemaGrammar();

    $grammar = new MySqlGrammar(DB::connection());
    $blueprint = new Blueprint(DB::connection(), 'products');
    $command = new Fluent(['index' => 'uniq_products_slug', 'columns' => ['slug'], 'where' => 'deleted_at is null']);

    expect(fn () => $grammar->compilePartialUnique($blueprint, $command))
        ->toThrow(RuntimeException::class, 'not supported on the [MySqlGrammar] driver');
});
