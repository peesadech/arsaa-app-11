<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_substitutions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('solution_id')->constrained('timetable_solutions')->cascadeOnDelete();
            $table->foreignId('timetable_entry_id')->nullable()->constrained('timetable_entries')->nullOnDelete();
            $table->foreignId('opened_course_id')->nullable()->constrained('opened_courses')->nullOnDelete();
            $table->foreignId('from_teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
            $table->foreignId('to_teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
            $table->string('action', 20); // substitute | unassign
            $table->unsignedTinyInteger('day');
            $table->unsignedTinyInteger('period');
            $table->string('reason', 500)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['solution_id', 'from_teacher_id'], 'idx_sub_from_teacher');
            $table->index(['solution_id', 'to_teacher_id'], 'idx_sub_to_teacher');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_substitutions');
    }
};
