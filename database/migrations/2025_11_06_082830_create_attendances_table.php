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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_session_id')
                ->constrained('lesson_sessions')
                ->onDelete('cascade');

            $table->foreignId('student_id')
                ->constrained('school_student_profiles')
                ->onDelete('cascade');

            $table->enum('status', [
                'present',  // var
                'absent',   // yok
                'late',     // geç
                'excused'   // izinli
            ]);

            $table->text('absent_excuse_note')->nullable();

            $table->timestamp('entered_at')->nullable(); // yoklamanın alındığı an
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
