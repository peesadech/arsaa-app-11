<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_term_courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('semester_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['teacher_id', 'academic_year_id', 'semester_id', 'course_id'], 'teacher_term_course_unique');
            $table->index(['academic_year_id', 'semester_id', 'course_id'], 'idx_term_course');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_term_courses');
    }
};
