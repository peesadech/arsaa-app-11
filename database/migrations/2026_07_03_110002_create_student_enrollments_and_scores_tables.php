<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ประวัติการเรียน: นักเรียน 1 คน มีได้หลายปี/เทอม แต่ละเทอมอยู่ 1 ห้อง
        Schema::create('student_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->foreignId('semester_id')->constrained('semesters')->cascadeOnDelete();
            $table->foreignId('grade_id')->constrained('grades')->cascadeOnDelete();
            $table->foreignId('classroom_id')->constrained('classrooms')->cascadeOnDelete();
            $table->string('status', 20)->default('enrolled'); // enrolled | moved | left
            $table->date('enrolled_at')->nullable();
            $table->string('note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // นักเรียน 1 คน active ได้ 1 ห้อง ต่อ ปี+เทอม (ห้องเดิมที่ย้ายออกจะเปลี่ยน status)
            $table->index(['academic_year_id', 'semester_id', 'grade_id', 'classroom_id'], 'idx_enroll_room');
            $table->index(['student_id', 'academic_year_id', 'semester_id'], 'idx_enroll_student_term');
        });

        // ผลการเรียน: อ้าง opened_courses ของระบบเดิม (ปี+เทอม+ระดับชั้น+ห้อง+วิชา)
        Schema::create('student_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('opened_course_id')->constrained('opened_courses')->cascadeOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
            $table->decimal('score_collect', 5, 2)->nullable();
            $table->decimal('score_midterm', 5, 2)->nullable();
            $table->decimal('score_final', 5, 2)->nullable();
            $table->decimal('total_score', 5, 2)->nullable();
            $table->string('grade', 10)->nullable();
            $table->string('result_status', 20)->nullable(); // pass | fail
            $table->string('remark')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['student_id', 'opened_course_id'], 'uniq_student_course_score');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_scores');
        Schema::dropIfExists('student_enrollments');
    }
};
