<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Shipu\Watchable\Traits\HasModelEvents;

test('booting a model that relies on the $observables fallback does not instantiate the model during its own boot cycle', function () {
    Schema::create('widgets', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->timestamps();
    });

    $model = new class extends Model {
        use HasModelEvents;

        protected $table = 'widgets';
        protected $guarded = [];
        protected $observables = ['custom_observable'];
    };

    expect(fn () => $model::create(['name' => 'a']))->not->toThrow(LogicException::class);
    expect($model::first()->name)->toBe('a');
});

test('an explicit $recordEvents list still takes priority over the $observables fallback', function () {
    Schema::create('gadgets', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->timestamps();
    });

    $model = new class extends Model {
        use HasModelEvents;

        protected $table = 'gadgets';
        protected $guarded = [];
        protected static $recordEvents = ['created'];
    };

    expect(fn () => $model::create(['name' => 'a']))->not->toThrow(LogicException::class);
});
