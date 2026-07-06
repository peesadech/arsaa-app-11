<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // บันทึกคะแนนความดี/ความชั่วของนักเรียนรายคน ต่อห้อง/เทอม/ปี (snapshot ค่าจาก behavior_scores)
        Schema::create('student_behavior_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->foreignId('semester_id')->constrained('semesters')->cascadeOnDelete();
            $table->foreignId('grade_id')->constrained('grades')->cascadeOnDelete();
            $table->foreignId('classroom_id')->constrained('classrooms')->cascadeOnDelete();
            $table->foreignId('behavior_score_id')->nullable()->constrained('behavior_scores')->nullOnDelete();
            $table->string('type', 10);      // merit | demerit (snapshot)
            $table->string('name');          // ชื่อรายการ (snapshot)
            $table->decimal('score', 5, 2);  // + สำหรับ merit, - สำหรับ demerit (snapshot)
            $table->string('note')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('recorded_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['academic_year_id', 'semester_id', 'grade_id', 'classroom_id'], 'idx_behavior_rec_room');
            $table->index('student_id', 'idx_behavior_rec_student');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_behavior_records');
    }
};
