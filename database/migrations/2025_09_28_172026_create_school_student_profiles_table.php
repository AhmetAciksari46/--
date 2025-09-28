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
        Schema::create('school_student_profiles', function (Blueprint $table) {
            $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
    
    // Ortak alanlar
    $table->string('phone')->nullable();
    $table->string('address')->nullable();

    // Öğrenciye özel
    $table->unsignedBigInteger('active_course_id')->nullable();
    $table->unsignedBigInteger('active_class_id')->nullable();
    $table->string('parent_name')->nullable();
    $table->string('parent_phone')->nullable();
            $table->date('birth_date');

    // Öğrenciye özel
    $table->unsignedBigInteger('schoolId')->constrained('schools')->onDelete('cascade');

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
