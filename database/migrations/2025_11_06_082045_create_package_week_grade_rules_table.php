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
        Schema::create('package_week_grade_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained()->cascadeOnDelete();
            $table->foreignId('grade_id')
                ->constrained('grades')
                ->cascadeOnDelete();
            $table->unsignedInteger('week_no');
            $table->unsignedTinyInteger('days_required');
            $table->unique(['package_id', 'grade_id', 'week_no']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('package_week_grade_rules');
    }
};
