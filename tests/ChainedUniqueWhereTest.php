<?php

use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

test('a plain ->unique() with no ->where() behaves exactly like stock Laravel', function () {
    Schema::create('products', function (Blueprint $table) {
        $table->id();
        $table->string('slug')->unique();
    });

    DB::table('products')->insert(['slug' => 'a']);

    expect(fn () => DB::table('products')->insert(['slug' => 'a']))
        ->toThrow(QueryException::class);
});

test('->string(...)->unique()->where(closure) allows reuse after soft delete', function () {
    Schema::create('products', function (Blueprint $table) {
        $table->id();
        $table->string('slug')->unique()->where(fn ($query) => $query->whereNull('deleted_at'));
        $table->timestamp('deleted_at')->nullable();
    });

    DB::table('products')->insert(['slug' => 'bar-council', 'deleted_at' => now()]);
    DB::table('products')->insert(['slug' => 'bar-council', 'deleted_at' => null]);

    expect(DB::table('products')->count())->toBe(2);
});

test('->string(...)->unique()->where(closure) still rejects a true duplicate', function () {
    Schema::create('products', function (Blueprint $table) {
        $table->id();
        $table->string('slug')->unique()->where(fn ($query) => $query->whereNull('deleted_at'));
        $table->timestamp('deleted_at')->nullable();
    });

    DB::table('products')->insert(['slug' => 'bar-council', 'deleted_at' => null]);

    expect(fn () => DB::table('products')->insert(['slug' => 'bar-council', 'deleted_at' => null]))
        ->toThrow(QueryException::class);
});

test('->string(...)->unique()->where(string) works the same as the closure form', function () {
    Schema::create('products', function (Blueprint $table) {
        $table->id();
        $table->string('slug')->unique()->where('deleted_at is null');
        $table->timestamp('deleted_at')->nullable();
    });

    DB::table('products')->insert(['slug' => 'a', 'deleted_at' => now()]);
    DB::table('products')->insert(['slug' => 'a', 'deleted_at' => null]);

    expect(DB::table('products')->count())->toBe(2);
});

test('$table->index(...)->where(...) works the same way for a non-unique index', function () {
    Schema::create('products', function (Blueprint $table) {
        $table->id();
        $table->string('status');
        $table->boolean('is_active')->default(true);
        $table->index('status')->where(fn ($query) => $query->where('is_active', true));
    });

    DB::table('products')->insert(['status' => 'a', 'is_active' => true]);
    DB::table('products')->insert(['status' => 'a', 'is_active' => true]);

    expect(DB::table('products')->count())->toBe(2);
});

test('the original $table->partialUnique(...) macro still works after the connection swap', function () {
    Schema::create('products', function (Blueprint $table) {
        $table->id();
        $table->string('slug');
        $table->timestamp('deleted_at')->nullable();

        $table->partialUnique('slug', 'uniq_products_slug', 'deleted_at is null');
    });

    DB::table('products')->insert(['slug' => 'a', 'deleted_at' => now()]);
    DB::table('products')->insert(['slug' => 'a', 'deleted_at' => null]);

    expect(DB::table('products')->count())->toBe(2);
});
