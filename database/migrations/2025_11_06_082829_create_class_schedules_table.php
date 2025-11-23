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
        Schema::create('class_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_model_id')->constrained()->onDelete('cascade');
            $table->foreignId('teacher_subject_id')->constrained('teacher_subjects')->onDelete('cascade');
            $table->foreignId('physical_classroom_id')->nullable()->constrained()->onDelete('set null');
            $table->string('day_of_week'); // örn: Monday, Tuesday
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('is_active');
            $table->boolean('is_successful')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_schedules');
    }
};
