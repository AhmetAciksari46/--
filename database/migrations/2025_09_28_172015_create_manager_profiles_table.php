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

           Schema::create('manager_profiles', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->string('phone')->nullable();
    $table->string('address')->nullable();
    $table->date('birth_date')->nullable();
    $table->string('note')->nullable();
    $table->string('referance')->nullable();
    $table->unsignedBigInteger('schoolId')->nullable();

    $table->boolean('payment_reminder')->default(false);
           });
    }
       


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manager_profiles');
    }
};
