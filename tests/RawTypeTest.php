<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

test('rawType() creates a column using an arbitrary SQL type verbatim', function () {
    Schema::create('topics', function (Blueprint $table) {
        $table->id();
        $table->rawType('path', 'ltree');
    });

    DB::table('topics')->insert(['path' => 'root.child']);

    expect(DB::table('topics')->first()->path)->toBe('root.child');
});

test('rawType() composes with standard column modifiers', function () {
    Schema::create('user_exam_presets', function (Blueprint $table) {
        $table->id();
        $table->rawType('topic_ids', 'bigint[]')->default('{}');
        $table->rawType('value_num', 'numeric')->nullable();
    });

    DB::table('user_exam_presets')->insert(['value_num' => null]);

    $row = DB::table('user_exam_presets')->first();

    expect($row->topic_ids)->toBe('{}')
        ->and($row->value_num)->toBeNull();
});
