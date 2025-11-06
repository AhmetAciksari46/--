<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_curriculum_overrides', function (Blueprint $table) {
            $table->id();

            // FK: kısa isimle tanımlıyoruz
            $table->unsignedBigInteger('school_student_profile_id');
            $table->foreign('school_student_profile_id', 'sco_profile_fk')
                ->references('id')
                ->on('school_student_profiles')
                ->onDelete('cascade');

            $table->unsignedTinyInteger('target_grade');
            $table->boolean('unlimited_access')->default(true);
            $table->timestamps();

            // UNIQUE: kısa isimle tanımlıyoruz (64 char altı!)
            $table->unique(
                ['school_student_profile_id', 'target_grade'],
                'sco_profile_grade_uq'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_curriculum_overrides');
    }
};
