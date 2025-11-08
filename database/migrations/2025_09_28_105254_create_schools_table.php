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

        Schema::create('schools', function (Blueprint $table) {
            $table->id(); // unsignedBigInteger
            $table->string('name');
            $table->string('nickname');
            $table->string('address')->nullable();
            $table->foreignId('manager_id')->constrained('users')->onDelete('cascade');
            $table->boolean('is_active')->default(false);
            $table->string('img_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('schools');
        Schema::enableForeignKeyConstraints();
    }
};
