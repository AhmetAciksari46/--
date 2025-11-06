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
        //TODO :ADDİTİONAL CLASSROOM İÇİN AYNI ŞEY YAPILACAK.

        Schema::create('school_student_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('schoolId');
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->unsignedBigInteger('active_course_id')->nullable();
            $table->foreignId('active_class_id')->nullable()->constrained('class_models')->nullOnDelete();
            $table->unsignedBigInteger('active_additional_class_id')->nullable();
            $table->string('parent_name')->nullable();
            $table->string('parent_phone')->nullable();
            $table->date('birth_date');
            $table->string('student_number')->unique();
            $table->string('tc_no')->unique();
            $table->string('gender')->nullable();
            $table->text('description')->nullable();
            $table->date('registered_at')->nullable();
            $table->string('img_path')->nullable();
            $table->string('status')->default('active');
            $table->boolean('is_active')->default(true);
            $table->enum('parent_status', ['evli', 'boşanmış'])->default('evli');
            $table->text('family_notes')->nullable();
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_student_profiles');
    }
};
