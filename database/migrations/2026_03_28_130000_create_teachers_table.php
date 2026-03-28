<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('phone')->nullable();
            $table->string('image_path')->nullable();
            $table->rememberToken();
            $table->tinyInteger('status')->default(1)->comment('1=Active, 2=Not Active');
            $table->timestamps();
        });

        Schema::create('course_teacher', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained()->onDelete('cascade');
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            $table->unique(['teacher_id', 'course_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_teacher');
        Schema::dropIfExists('teachers');
    }
};
