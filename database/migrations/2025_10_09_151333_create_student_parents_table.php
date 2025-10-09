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
        Schema::create('student_parents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_student_profile_id')->constrained('school_student_profiles')->onDelete('cascade');
            $table->enum('type', ['anne', 'baba', 'vasi'])->default('anne');
            $table->string('relationship')->nullable();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('tc_no')->nullable();
            $table->string('job')->nullable();
            $table->string('address')->nullable();
            $table->date('birth_date')->nullable();
            $table->enum('status', ['hayatta', 'vefat'])->default('hayatta');
            $table->boolean('is_parent')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_parents');
    }
};
