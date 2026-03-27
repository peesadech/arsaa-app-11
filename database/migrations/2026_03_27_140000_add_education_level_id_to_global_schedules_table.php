<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('global_schedules', function (Blueprint $table) {
            $table->foreignId('education_level_id')->nullable()->after('id')->constrained('education_levels')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('global_schedules', function (Blueprint $table) {
            $table->dropForeign(['education_level_id']);
            $table->dropColumn('education_level_id');
        });
    }
};
