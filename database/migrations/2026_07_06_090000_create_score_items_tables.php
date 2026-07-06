<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // รายการคะแนนของแต่ละวิชาที่เปิดสอน (เพิ่มเองได้: ชิ้นงาน/ควิซ/กลางภาค/ปลายภาค/คุณลักษณะ ฯลฯ)
        Schema::create('score_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('opened_course_id')->constrained('opened_courses')->cascadeOnDelete();
            $table->string('category', 20)->default('other'); // assignment|homework|quiz|practical|midterm|final|extra|attribute|reading|special|other
            $table->string('name');
            $table->decimal('full_score', 6, 2)->default(0);   // คะแนนเต็มของรายการ
            $table->decimal('weight', 6, 2)->nullable();       // น้ำหนัก (คะแนนที่รายการนี้คิดเข้าคะแนนรวม); null = ใช้คะแนนดิบ
            $table->boolean('counts_toward_total')->default(true); // false = บันทึกแยก ไม่คิดเข้าเกรด (เช่น คุณลักษณะ/อ่านคิดวิเคราะห์)
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['opened_course_id', 'sort_order'], 'idx_score_item_course_sort');
        });

        // คะแนนของนักเรียนต่อรายการ
        Schema::create('student_score_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('score_item_id')->constrained('score_items')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->decimal('score', 6, 2)->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['score_item_id', 'student_id'], 'uniq_score_item_student');
            $table->index('student_id', 'idx_ssi_student');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_score_items');
        Schema::dropIfExists('score_items');
    }
};
