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

            // STUDENT INFO
            $table->string('student_name');
            $table->string('student_surname');
            $table->string('student_tc')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('gender')->nullable();
            $table->string('student_phone')->nullable();
            $table->string('student_email')->nullable();
            $table->text('address')->nullable();

            // PARENT INFO (JSON)
            $table->json('mother')->nullable(); // { name, phone, job, email, birth_date }
            $table->json('father')->nullable();

            $table->string('parent_status')->nullable();
            // together | separated | mother_dead | father_dead | both_dead

            // EXTRA
            $table->text('description')->nullable();
            $table->json('notes')->nullable(); // note1, note2, note3

            // STATE
            $table->string('status')->default('draft');
            // draft | submitted | approved | cancelled

            // AUDIT
            $table->foreignId('created_by')->constrained('users');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
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
