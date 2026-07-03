<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // รูปแบบการคิดเกรด
        Schema::create('grading_schemes', function (Blueprint $table) {
            $table->id();
            $table->string('name');                    // ชื่อรูปแบบ
            $table->string('result_type', 20);         // รูปแบบผล: grade (A-F) | pass_fail (ผ่าน/ไม่ผ่าน)
            $table->text('description')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });

        // รายละเอียดช่วงคะแนน เช่น คะแนน >= 80 → A, 70 <= คะแนน < 80 → B
        Schema::create('grading_scheme_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grading_scheme_id')->constrained('grading_schemes')->cascadeOnDelete();
            $table->decimal('min_score', 5, 2);                 // คะแนนต่ำสุด
            $table->decimal('max_score', 5, 2);                 // คะแนนสูงสุด
            $table->string('result_th', 50);                    // ผล (ไทย) เช่น A, ผ่าน
            $table->string('result_en', 50)->nullable();        // ผล (อังกฤษ) เช่น A, Pass
            $table->string('result_cn', 50)->nullable();        // ผล (จีน) เช่น A, 通过
            $table->string('description')->nullable();          // คำอธิบายต่อแถว
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grading_scheme_details');
        Schema::dropIfExists('grading_schemes');
    }
};
