<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_term_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('semester_id')->constrained()->cascadeOnDelete();

            $table->string('status', 30)->default('available');
            $table->boolean('can_be_scheduled')->default(true);
            $table->unsignedTinyInteger('max_periods_per_day')->nullable();
            $table->unsignedSmallInteger('max_periods_per_week')->nullable();
            $table->date('effective_from')->nullable();
            $table->date('effective_until')->nullable();
            $table->text('notes')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['teacher_id', 'academic_year_id', 'semester_id'], 'teacher_term_unique');
            $table->index(['academic_year_id', 'semester_id', 'can_be_scheduled'], 'idx_term_schedulable');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_term_statuses');
    }
};
