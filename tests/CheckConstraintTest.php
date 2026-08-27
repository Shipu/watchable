<?php

use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

test('check() enforces a single-column constraint', function () {
    Schema::create('products', function (Blueprint $table) {
        $table->id();
        $table->decimal('total', 10, 2);

        $table->check('total >= 0', 'chk_total_positive');
    });

    DB::table('products')->insert(['total' => 10]);

    expect(fn () => DB::table('products')->insert(['total' => -5]))
        ->toThrow(QueryException::class);
});

test('check() enforces a multi-column business rule', function () {
    Schema::create('orders', function (Blueprint $table) {
        $table->id();
        $table->decimal('subtotal', 10, 2);
        $table->decimal('discount_amount', 10, 2)->default(0);
        $table->decimal('total', 10, 2);

        $table->check('total = subtotal - discount_amount AND total >= 0', 'chk_total');
    });

    DB::table('orders')->insert(['subtotal' => 100, 'discount_amount' => 10, 'total' => 90]);

    expect(fn () => DB::table('orders')->insert(['subtotal' => 100, 'discount_amount' => 10, 'total' => 80]))
        ->toThrow(QueryException::class);
});

test('dropCheck() removes a previously added constraint', function () {
    Schema::create('products', function (Blueprint $table) {
        $table->id();
        $table->decimal('total', 10, 2);

        $table->check('total >= 0', 'chk_total_positive');
    });

    Schema::table('products', function (Blueprint $table) {
        $table->dropCheck('chk_total_positive');
    });

    DB::table('products')->insert(['total' => -5]);

    expect(DB::table('products')->count())->toBe(1);
});
