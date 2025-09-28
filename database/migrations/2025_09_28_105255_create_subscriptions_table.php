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
   Schema::create('subscriptions', function (Blueprint $table) {
    $table->id();

    // Okul ile ilişki (nullable olabilir ama FK düzgün tanımlanmalı)
    $table->unsignedBigInteger('school_id')->nullable();
    $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');

    // Kullanıcı ile ilişki
    $table->unsignedBigInteger('user_id')->nullable();
    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

    // Paket ile ilişki (nullable değil)
    $table->foreignId('package_id')->constrained('packages')->onDelete('cascade');

    $table->date('start_date');
    $table->date('end_date');
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});



    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
