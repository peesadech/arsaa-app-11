<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Master data แบบรวม type: nationality (ใช้ทั้งเชื้อชาติ/สัญชาติ), religion,
        // blood_type, document_type, guardian_type, province
        Schema::create('master_options', function (Blueprint $table) {
            $table->id();
            $table->string('type', 40)->index();
            $table->string('name_th');
            $table->string('name_en')->nullable();
            $table->string('name_cn')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->tinyInteger('status')->default(1);
            $table->timestamps();

            $table->unique(['type', 'name_th']);
        });

        // เกณฑ์เกรด เช่น 80-100 = A (ผ่าน)
        Schema::create('grade_settings', function (Blueprint $table) {
            $table->id();
            $table->string('grade', 10);
            $table->decimal('min_score', 5, 2);
            $table->decimal('max_score', 5, 2);
            $table->boolean('is_pass')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grade_settings');
        Schema::dropIfExists('master_options');
    }
};
