<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('global_schedules', function (Blueprint $table) {
            $table->id();
            $table->json('teaching_days')->nullable();       // ["1","2","3","4","5"]
            $table->time('start_time')->nullable();          // "08:00:00"
            $table->unsignedSmallInteger('period_duration')->default(50); // นาทีต่อคาบ (global)
            $table->json('day_configs')->nullable();         // {"1":{"periods":8,"breaks":{"2":10}},...}
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('global_schedules');
    }
};
