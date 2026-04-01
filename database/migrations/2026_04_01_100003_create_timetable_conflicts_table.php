<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timetable_conflicts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('solution_id')->constrained('timetable_solutions')->cascadeOnDelete();
            $table->string('type');       // teacher_clash, room_clash, classroom_clash, teacher_unavailable, room_unavailable, period_count_mismatch
            $table->string('severity');   // hard, soft
            $table->unsignedTinyInteger('day')->nullable();
            $table->unsignedTinyInteger('period')->nullable();
            $table->json('details');
            $table->timestamps();

            $table->index(['solution_id', 'type']);
            $table->index(['solution_id', 'severity']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timetable_conflicts');
    }
};
