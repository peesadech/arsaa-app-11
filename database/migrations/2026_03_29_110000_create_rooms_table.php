<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('room_number');
            $table->foreignId('building_id')->constrained('buildings')->cascadeOnDelete();
            $table->text('description')->nullable();
            $table->tinyInteger('status')->default(1)->comment('1: Active, 2: Inactive');
            $table->timestamps();
        });

        Schema::create('course_room', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('rooms')->cascadeOnDelete();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['room_id', 'course_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_room');
        Schema::dropIfExists('rooms');
    }
};
