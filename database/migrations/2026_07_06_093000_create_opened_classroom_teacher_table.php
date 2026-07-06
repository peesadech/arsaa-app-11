<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ครูประจำชั้น (หลายคนต่อห้อง): ประจำชั้นหลัก / ร่วม — ผูกกับห้องที่เปิดต่อปี+เทอม
        Schema::create('opened_classroom_teacher', function (Blueprint $table) {
            $table->id();
            $table->foreignId('opened_classroom_id')->constrained('opened_classrooms')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('teachers')->cascadeOnDelete();
            $table->string('role', 10)->default('main'); // main | co
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['opened_classroom_id', 'teacher_id'], 'uniq_opened_classroom_teacher');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opened_classroom_teacher');
    }
};
