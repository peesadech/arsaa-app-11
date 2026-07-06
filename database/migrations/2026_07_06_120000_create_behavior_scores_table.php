<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // คะแนนความดี (merit, > 0) / คะแนนความชั่ว (demerit, < 0) — master ชื่อ + คะแนน
        Schema::create('behavior_scores', function (Blueprint $table) {
            $table->id();
            $table->string('type', 10); // merit | demerit
            $table->string('name');
            $table->decimal('score', 5, 2); // merit เป็นบวก, demerit เป็นลบ
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['type', 'sort_order'], 'idx_behavior_type_sort');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('behavior_scores');
    }
};
