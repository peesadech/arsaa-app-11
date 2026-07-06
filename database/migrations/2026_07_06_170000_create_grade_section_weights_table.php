<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // สัดส่วนช่วงคะแนน กลางภาค/ปลายภาค/เก็บ (期中/期末/平时) ต่อระดับชั้น ตามปี+เทอม — รวมควร = 100
        Schema::create('grade_section_weights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->foreignId('semester_id')->constrained('semesters')->cascadeOnDelete();
            $table->foreignId('grade_id')->constrained('grades')->cascadeOnDelete();
            $table->decimal('midterm_weight', 5, 2)->default(35);
            $table->decimal('final_weight', 5, 2)->default(35);
            $table->decimal('collect_weight', 5, 2)->default(30);
            $table->timestamps();

            $table->unique(['academic_year_id', 'semester_id', 'grade_id'], 'uniq_grade_section_weight');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grade_section_weights');
    }
};
