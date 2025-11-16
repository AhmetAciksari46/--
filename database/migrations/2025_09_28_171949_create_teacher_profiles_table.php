<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('teacher_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('img_path')->nullable();
            $table->string('status')->default('active');

            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');

            $table->boolean('is_active')->default(true);
            $table->string('gender')->nullable();
            $table->date('birth_date')->nullable();
            $table->date('start_date')->nullable();
            $table->text('description')->nullable();
            $table->string('color_code')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->string('emergency_contact_relationship')->nullable();
            $table->text('emergency_contact_description')->nullable();

            $table->timestamps();
        });
    }



    public function down(): void
    {
        Schema::dropIfExists('teacher_profiles');
    }
};
