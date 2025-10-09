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
        Schema::create('student_health_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_student_profile_id')->constrained('school_student_profiles')->onDelete('cascade');
            $table->boolean('has_chronic_disease')->default(false);
            $table->text('chronic_disease_description')->nullable();
            $table->text('allergies')->nullable();
            $table->text('medications')->nullable();
            $table->text('special_needs')->nullable();
            $table->string('doctor_name')->nullable();
            $table->string('doctor_phone')->nullable();
            $table->enum('blood_type', ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-', 'bilinmiyor'])->default('bilinmiyor');
            $table->enum('health_insurance', ['SGK', 'özel sağlık sigortası', 'yeşil kart', 'sigortasız', 'diğer'])->default('diğer');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_health_profiles');
    }
};
