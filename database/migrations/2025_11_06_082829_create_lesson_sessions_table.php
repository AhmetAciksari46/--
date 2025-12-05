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
        Schema::create('lesson_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_schedule_id')
                ->constrained('class_schedules')
                ->onDelete('cascade');

            $table->integer('week_number'); // 1 - 40
            $table->date('date');           // Örn: 2025-11-11

            $table->foreignId('teacher_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');

            $table->foreignId('physical_classroom_id')
                ->nullable()
                ->constrained('physical_classrooms')
                ->onDelete('set null');

            $table->enum('status', [
                'scheduled',   // planlandı
                'completed',   // yoklama alındı ve ders işlendi
                'cancelled',   // iptal (kar tatili vb.)
                'skipped',     // öğretmen/öğrenci gelmedi, ders işlemedi
                'no_school'    // resmi tatil, okul tatili
            ])->default('scheduled');

            $table->boolean('is_attendance_required')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lesson_sessions');
    }
};
