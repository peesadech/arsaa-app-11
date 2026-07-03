<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('student_code', 30)->unique();
            $table->string('image_path')->nullable();
            $table->string('name_th');
            $table->string('name_cn')->nullable();
            $table->string('citizen_id', 20)->nullable();
            $table->date('birth_date')->nullable();
            $table->foreignId('race_id')->nullable()->constrained('master_options')->nullOnDelete();
            $table->foreignId('nationality_id')->nullable()->constrained('master_options')->nullOnDelete();
            $table->foreignId('religion_id')->nullable()->constrained('master_options')->nullOnDelete();
            $table->foreignId('blood_type_id')->nullable()->constrained('master_options')->nullOnDelete();
            $table->decimal('height', 5, 1)->nullable();
            $table->decimal('weight', 5, 1)->nullable();
            $table->string('chronic_disease')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('mobile', 50)->nullable();
            $table->string('status', 20)->default('studying'); // studying | suspended | resigned | graduated
            $table->text('note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('name_th');
            $table->index('name_cn');
            $table->index('status');
        });

        // ที่อยู่ 2 ชุด: current (ปัจจุบัน) / registered (ตามทะเบียนบ้าน)
        Schema::create('student_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('type', 20); // current | registered
            $table->string('house_no', 50)->nullable();
            $table->string('moo', 20)->nullable();
            $table->string('subdistrict')->nullable();
            $table->string('district')->nullable();
            $table->foreignId('province_id')->nullable()->constrained('master_options')->nullOnDelete();
            $table->string('postal_code', 10)->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'type']);
        });

        Schema::create('student_guardians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('guardian_type_id')->nullable()->constrained('master_options')->nullOnDelete();
            $table->string('name');
            $table->string('name_cn')->nullable();
            $table->unsignedTinyInteger('age')->nullable();
            $table->foreignId('race_id')->nullable()->constrained('master_options')->nullOnDelete();
            $table->foreignId('nationality_id')->nullable()->constrained('master_options')->nullOnDelete();
            $table->foreignId('religion_id')->nullable()->constrained('master_options')->nullOnDelete();
            $table->string('living_status', 20)->nullable(); // alive | deceased | together | divorced | other
            $table->string('address')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('occupation')->nullable();
            $table->string('workplace_address')->nullable();
            $table->string('relationship')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
        });

        Schema::create('student_education_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('school_name');
            $table->string('school_location')->nullable();
            $table->string('last_level')->nullable();
            $table->decimal('gpa', 4, 2)->nullable();
            $table->string('graduated_at', 20)->nullable(); // เดือน/ปี แบบข้อความ
            $table->string('note')->nullable();
            $table->timestamps();
        });

        Schema::create('student_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('document_type_id')->constrained('master_options')->cascadeOnDelete();
            $table->boolean('is_received')->default(false);
            $table->string('file_path')->nullable();
            $table->string('note')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'document_type_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_documents');
        Schema::dropIfExists('student_education_histories');
        Schema::dropIfExists('student_guardians');
        Schema::dropIfExists('student_addresses');
        Schema::dropIfExists('students');
    }
};
