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
        Schema::create('student_pre_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            // --- Student Info ---
            $table->string('first_name');
            $table->string('last_name');
            $table->string('tc', 11)->nullable()->unique();
            $table->foreignId('grade_id')
                ->constrained('grades')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->enum('gender', ['male', 'female'])->nullable();
            $table->date('birth_date')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();

            // --- Parent Info (Mother) ---
            $table->string('mother_full_name')->nullable();
            $table->string('mother_phone')->nullable();
            $table->string('mother_job')->nullable();
            $table->date('mother_birth_date')->nullable();
            $table->string('mother_email')->nullable();

            // --- Parent Info (Father) ---
            $table->string('father_full_name')->nullable();
            $table->string('father_phone')->nullable();
            $table->string('father_job')->nullable();
            $table->date('father_birth_date')->nullable();
            $table->string('father_email')->nullable();

            // ✅ Anne-baba durumu (resimdeki dropdown)
            $table->enum('parents_status', [
                'together_alive',   // Anne baba sağ ve birlikte
                'separate_alive',   // Anne baba sağ ama ayrı
                'mother_deceased',  // Anne vefat
                'father_deceased',  // Baba vefat
                'both_deceased',    // Anne ve baba vefat
            ])->nullable(); // "-" karşılığı

            // --- Notes ---
            $table->text('description')->nullable();
            $table->text('note_1')->nullable();
            $table->text('note_2')->nullable();
            $table->text('note_3')->nullable();

            // ✅ Statü (resimdeki dropdown)
            $table->enum('status', [
                'in_progress',   // Görüşülüyor
                'form_request',  // Form Talepleri
                'saved',         // Kaydedildi
                'cancelled',     // İptal edildi
            ])->default('in_progress');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_pre_registrations');
    }
};
