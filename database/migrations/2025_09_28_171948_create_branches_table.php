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
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('name');              // Matematik, Türkçe vs.
            $table->string('slug')->unique();    // URL dostu isim
            $table->string('code', 10)->nullable(); // MATH, TR gibi kısa kod
            $table->text('description')->nullable();
            $table->string('color', 20)->nullable(); // UI renk
            $table->string('icon', 50)->nullable();  // UI ikon adı
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teacher_profiles', function (Blueprint $table) {
            $table->dropConstrainedForeignId('branch_id');
        });
    }
};
