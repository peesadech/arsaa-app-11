<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name_th');
            $table->string('name_en')->nullable();
            // PRESENT / LATE / LEAVE / ABSENT / ACTIVITY / COMPETITION / SICK / ...
            $table->string('status_type')->nullable();
            $table->boolean('is_count_as_present')->default(false);
            $table->boolean('is_count_as_absent')->default(false);
            $table->boolean('is_late')->default(false);
            $table->boolean('is_leave')->default(false);
            $table->boolean('is_require_remark')->default(false);
            $table->string('color', 20)->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('is_active');
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_statuses');
    }
};
