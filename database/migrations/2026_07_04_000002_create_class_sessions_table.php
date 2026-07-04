<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->foreignId('semester_id')->constrained('semesters')->cascadeOnDelete();
            // Schedule linkage (nullable = supports ad-hoc sessions not from a generated timetable)
            $table->foreignId('timetable_entry_id')->nullable()->constrained('timetable_entries')->nullOnDelete();
            $table->foreignId('opened_course_id')->nullable()->constrained('opened_courses')->nullOnDelete();
            // Denormalized for fast reporting/indexing (subject = course)
            $table->foreignId('course_id')->nullable()->constrained('courses')->nullOnDelete();
            $table->foreignId('grade_id')->nullable()->constrained('grades')->nullOnDelete();
            $table->foreignId('classroom_id')->nullable()->constrained('classrooms')->nullOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
            $table->date('session_date');
            $table->unsignedTinyInteger('day')->nullable();     // 1=Mon .. 7=Sun
            $table->unsignedTinyInteger('period')->nullable();
            $table->string('start_time')->nullable();
            $table->string('end_time')->nullable();
            // OPEN / CLOSED / CANCELLED / POSTPONED
            $table->string('status')->default('OPEN');
            $table->text('remark')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('session_date');
            $table->index('teacher_id');
            $table->index('classroom_id');
            $table->index('course_id');
            $table->index(['academic_year_id', 'semester_id']);
            // Prevent duplicate session for the same timetable slot on the same day
            $table->unique(['timetable_entry_id', 'session_date'], 'session_entry_date_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_sessions');
    }
};
