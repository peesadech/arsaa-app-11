<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Attendance detail (one row per student per session)
        Schema::create('class_session_students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_session_id')->constrained('class_sessions')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('attendance_status_id')->nullable()->constrained('attendance_statuses')->nullOnDelete();
            $table->string('arrival_time')->nullable();
            $table->text('remark')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['class_session_id', 'student_id'], 'session_student_unique');
            $table->index('student_id');
            $table->index('attendance_status_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_session_students');
    }
};
