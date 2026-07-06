<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // หัวหน้ากลุ่มสาระ — ใช้เป็นผู้ตรวจ (Academic Review) ในขั้นตอนอนุมัติผลการเรียน
        Schema::table('subject_groups', function (Blueprint $table) {
            $table->foreignId('head_teacher_id')->nullable()->after('description')
                ->constrained('teachers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('subject_groups', function (Blueprint $table) {
            $table->dropConstrainedForeignId('head_teacher_id');
        });
    }
};
