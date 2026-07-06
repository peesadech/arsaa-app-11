<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // น้ำหนัก/หน่วยกิตของรายวิชา ต่อ (ปี+เทอม+ห้อง+วิชา) — ใช้ถ่วงน้ำหนักคิด GPA/GPAX ข้ามวิชา
        Schema::table('opened_courses', function (Blueprint $table) {
            $table->decimal('weight', 4, 1)->nullable()->after('course_id')
                ->comment('น้ำหนัก/หน่วยกิตของรายวิชา');
        });
    }

    public function down(): void
    {
        Schema::table('opened_courses', function (Blueprint $table) {
            $table->dropColumn('weight');
        });
    }
};
