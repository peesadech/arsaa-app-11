<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ประเภทวิชา: รูปแบบการคิดเกรด default
        Schema::table('course_types', function (Blueprint $table) {
            $table->foreignId('grading_scheme_id')->nullable()->after('description')
                ->constrained('grading_schemes')->nullOnDelete();
        });

        // รายวิชา: override รูปแบบการคิดเกรด (ไม่ตั้ง = ใช้ตามประเภทวิชา)
        Schema::table('courses', function (Blueprint $table) {
            $table->foreignId('grading_scheme_id')->nullable()->after('course_type_id')
                ->constrained('grading_schemes')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropForeign(['grading_scheme_id']);
            $table->dropColumn('grading_scheme_id');
        });

        Schema::table('course_types', function (Blueprint $table) {
            $table->dropForeign(['grading_scheme_id']);
            $table->dropColumn('grading_scheme_id');
        });
    }
};
