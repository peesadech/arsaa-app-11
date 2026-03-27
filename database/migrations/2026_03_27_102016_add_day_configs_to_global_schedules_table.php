<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('global_schedules', function (Blueprint $table) {
            if (!Schema::hasColumn('global_schedules', 'day_configs')) {
                $table->json('day_configs')->nullable()->after('period_duration');
            }
        });
    }

    public function down(): void
    {
        Schema::table('global_schedules', function (Blueprint $table) {
            $table->dropColumn('day_configs');
        });
    }
};
