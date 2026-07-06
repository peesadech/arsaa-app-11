<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // บันทึกประวัติการเปลี่ยนแปลงเกรด/ผล (override, ผลพิเศษ, workflow) เพื่อ audit
        Schema::create('student_score_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_score_id')->constrained('student_scores')->cascadeOnDelete();
            $table->string('action', 30);          // override | clear_override | special | submit | approve | publish | reject ...
            $table->string('from_value')->nullable();
            $table->string('to_value')->nullable();
            $table->string('reason')->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('student_score_id', 'idx_score_log_score');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_score_logs');
    }
};
