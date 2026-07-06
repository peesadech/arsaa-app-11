<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_scores', function (Blueprint $table) {
            // ผลพิเศษ: ร / มส / มผ / ผ / ขส (ตั้งเอง ไม่คิดจากคะแนน)
            $table->string('special_result', 10)->nullable()->after('result_status');
            // override เกรดด้วยมือ (ไม่ให้ระบบคำนวณทับ)
            $table->boolean('is_override')->default(false)->after('special_result');
            $table->string('override_reason')->nullable()->after('is_override');
            $table->foreignId('graded_by')->nullable()->after('override_reason')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('student_scores', function (Blueprint $table) {
            $table->dropConstrainedForeignId('graded_by');
            $table->dropColumn(['special_result', 'is_override', 'override_reason']);
        });
    }
};
