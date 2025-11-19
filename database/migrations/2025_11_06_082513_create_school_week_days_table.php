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
        Schema::create('school_week_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_week_id')->constrained('school_weeks')->onDelete('cascade');

            $table->unsignedInteger('day_no'); // 1,2,3,...
            $table->date('real_date');


            $table->unique(['school_week_id', 'day_no']); // Aynı gün tekrar eklenmesin
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_week_days');
    }
};
