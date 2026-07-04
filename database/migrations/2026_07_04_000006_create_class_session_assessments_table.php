<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_session_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_session_id')->constrained('class_sessions')->cascadeOnDelete();
            $table->string('type')->default('quiz'); // quiz / assignment / participation / score
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('max_score', 8, 2)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('class_session_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_session_assessments');
    }
};
