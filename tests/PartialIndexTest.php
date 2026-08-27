<?php

use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Schema::create('products', function (Blueprint $table) {
        $table->id();
        $table->string('slug');
        $table->boolean('is_active')->default(true);
        $table->timestamp('deleted_at')->nullable();
    });
});

test('partialUnique allows the same value once the original row is soft-deleted', function () {
    Schema::partialUnique('products', 'slug', 'uniq_products_slug', 'deleted_at is null');

    DB::table('products')->insert(['slug' => 'bar-council', 'deleted_at' => now()]);
    DB::table('products')->insert(['slug' => 'bar-council', 'deleted_at' => null]);

    expect(DB::table('products')->count())->toBe(2);
});

test('partialUnique rejects a true duplicate among non-deleted rows', function () {
    Schema::partialUnique('products', 'slug', 'uniq_products_slug', 'deleted_at is null');

    DB::table('products')->insert(['slug' => 'bar-council', 'deleted_at' => null]);

    expect(fn () => DB::table('products')->insert(['slug' => 'bar-council', 'deleted_at' => null]))
        ->toThrow(QueryException::class);
});

test('partialIndex creates a working, non-unique conditional index', function () {
    Schema::partialIndex('products', 'slug', 'idx_active_products_slug', 'is_active');

    DB::table('products')->insert(['slug' => 'a', 'is_active' => true]);
    DB::table('products')->insert(['slug' => 'a', 'is_active' => true]);

    expect(DB::table('products')->count())->toBe(2);
});

test('dropPartialUnique removes a previously created partial unique index', function () {
    Schema::partialUnique('products', 'slug', 'uniq_products_slug', 'deleted_at is null');
    Schema::dropPartialUnique('uniq_products_slug');

    DB::table('products')->insert(['slug' => 'bar-council', 'deleted_at' => null]);
    DB::table('products')->insert(['slug' => 'bar-council', 'deleted_at' => null]);

    expect(DB::table('products')->count())->toBe(2);
});
