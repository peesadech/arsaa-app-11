<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_term_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_term_status_id')->constrained('teacher_term_statuses')->cascadeOnDelete();
            $table->string('old_status', 30)->nullable();
            $table->string('new_status', 30);
            $table->boolean('old_can_be_scheduled')->nullable();
            $table->boolean('new_can_be_scheduled');
            $table->text('reason')->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('changed_at');

            $table->index('teacher_term_status_id', 'idx_log_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_term_status_logs');
    }
};
