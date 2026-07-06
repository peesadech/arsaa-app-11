<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ย้าย weight ไปกำหนดที่ระดับชั้น (course_weights) แทนที่จะเป็นราย opened_course
        Schema::table('opened_courses', function (Blueprint $table) {
            if (Schema::hasColumn('opened_courses', 'weight')) {
                $table->dropColumn('weight');
            }
        });
    }

    public function down(): void
    {
        Schema::table('opened_courses', function (Blueprint $table) {
            $table->decimal('weight', 4, 1)->nullable()->after('course_id');
        });
    }
};
