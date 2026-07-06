<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // สัดส่วน/น้ำหนักของรายวิชา ต่อระดับชั้น ตามปีการศึกษา+เทอม (รวมทุกวิชาในระดับชั้นควร = 100)
        Schema::create('course_weights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->foreignId('semester_id')->constrained('semesters')->cascadeOnDelete();
            $table->foreignId('grade_id')->constrained('grades')->cascadeOnDelete();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->decimal('weight', 5, 2)->default(0); // สัดส่วน % เช่น 35.00
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['academic_year_id', 'semester_id', 'grade_id', 'course_id'], 'uniq_course_weight');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_weights');
    }
};
