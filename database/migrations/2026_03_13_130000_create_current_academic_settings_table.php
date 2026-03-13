<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('current_academic_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('cascade')->comment('ปีการศึกษาที่เลือก');
            $table->foreignId('semester_id')->constrained('semesters')->onDelete('cascade')->comment('ภาคเรียนที่เลือก');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('current_academic_settings');
    }
};
