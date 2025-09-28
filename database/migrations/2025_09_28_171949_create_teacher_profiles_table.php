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

                   Schema::create('teacher_profiles', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    
    // Ortak alanlar
    $table->string('phone')->nullable();
    $table->string('address')->nullable();

    // Öğrenciye özel
    $table->unsignedBigInteger('schoolId')->constrained('schools')->onDelete('cascade');

    // Manager’a özel
           });

    } 

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_profiles');
    }
};
