<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // หัวข้อประเมินความประพฤติ 操行评量 (礼貌/衣着/服务/纪律)
        Schema::create('conduct_criteria', function (Blueprint $table) {
            $table->id();
            $table->string('name');            // ชื่อไทย/หลัก
            $table->string('name_cn')->nullable();
            $table->decimal('max_score', 5, 2)->default(100);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'sort_order'], 'idx_conduct_active_sort');
        });

        // คะแนนความประพฤติรายคน ต่อหัวข้อ/เทอม
        Schema::create('student_conduct_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->foreignId('semester_id')->constrained('semesters')->cascadeOnDelete();
            $table->foreignId('conduct_criterion_id')->constrained('conduct_criteria')->cascadeOnDelete();
            $table->decimal('score', 5, 2)->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['student_id', 'academic_year_id', 'semester_id', 'conduct_criterion_id'], 'uniq_student_conduct');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_conduct_scores');
        Schema::dropIfExists('conduct_criteria');
    }
};
