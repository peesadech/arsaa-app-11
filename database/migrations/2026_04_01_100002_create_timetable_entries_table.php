<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timetable_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('solution_id')->constrained('timetable_solutions')->cascadeOnDelete();
            $table->foreignId('opened_course_id')->constrained('opened_courses')->cascadeOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
            $table->foreignId('room_id')->nullable()->constrained('rooms')->nullOnDelete();
            $table->unsignedTinyInteger('day');
            $table->unsignedTinyInteger('period');
            $table->boolean('is_locked')->default(false);
            $table->timestamps();

            $table->unique(['solution_id', 'opened_course_id', 'day', 'period'], 'entry_unique_slot');
            $table->index(['solution_id', 'teacher_id', 'day', 'period'], 'idx_teacher_conflict');
            $table->index(['solution_id', 'room_id', 'day', 'period'], 'idx_room_conflict');
            $table->index(['solution_id', 'day', 'period'], 'idx_day_period');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timetable_entries');
    }
};
