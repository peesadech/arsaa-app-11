<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // สถานะ workflow ผลการเรียนต่อรายวิชาที่เปิดสอน:
        // draft → submitted (ครูส่ง) → reviewed (หัวหน้ากลุ่มสาระตรวจ) → approved (งานทะเบียน) → published
        Schema::create('course_result_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('opened_course_id')->constrained('opened_courses')->cascadeOnDelete();
            $table->string('status', 20)->default('draft');

            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable();

            $table->string('reject_reason')->nullable();
            $table->timestamps();

            $table->unique('opened_course_id', 'uniq_result_submission_course');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_result_submissions');
    }
};
