<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->unsignedTinyInteger('periods_per_week')->default(1)->after('subject_group_id')->comment('จำนวนคาบต่อสัปดาห์');
            $table->json('preferred_days')->nullable()->after('periods_per_week')->comment('วันที่ต้องการจัดตารางเรียน');
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn(['periods_per_week', 'preferred_days']);
        });
    }
};
