<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timetable_solutions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('generation_id')->constrained('timetable_generations')->cascadeOnDelete();
            $table->unsignedInteger('rank')->default(0);
            $table->decimal('fitness_score', 12, 4)->default(0);
            $table->unsignedInteger('hard_violations')->default(0);
            $table->unsignedInteger('soft_violations')->default(0);
            $table->json('fitness_breakdown')->nullable();
            $table->boolean('is_selected')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timetable_solutions');
    }
};
