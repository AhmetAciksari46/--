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
        Schema::create('contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('grade'); // hangi sınıfa ait
            $table->unsignedInteger('week_no'); // hangi haftaya ait
            $table->unsignedTinyInteger('day_index')->nullable(); // hangi güne ait (A modunda)
            $table->foreignId('subject_id')->nullable()->constrained()->nullOnDelete(); // hangi derse ait
            $table->enum('type', ['homework', 'quiz', 'note'])->default('homework');
            $table->string('title'); // içeriğin başlığı
            $table->json('payload')->nullable(); // soru seti / açıklama / json içerik
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contents');
    }
};
