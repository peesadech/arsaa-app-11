<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('yearly_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->foreignId('semester_id')->constrained('semesters')->cascadeOnDelete();
            $table->foreignId('education_level_id')->constrained('education_levels')->cascadeOnDelete();
            $table->json('teaching_days')->nullable();
            $table->string('start_time')->nullable();
            $table->integer('period_duration')->default(50);
            $table->json('day_configs')->nullable();
            $table->timestamps();

            $table->unique(['academic_year_id', 'semester_id', 'education_level_id'], 'yearly_schedule_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('yearly_schedules');
    }
};
