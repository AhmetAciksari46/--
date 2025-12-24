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
        Schema::create('groups', function (Blueprint $table) {
            $table->id();

            // Global / school / classroom ayrımı
            $table->string('type'); // GroupType string olarak tutulacak

            // Okul & sınıf bağları (varsa)
            $table->unsignedBigInteger('school_id')->nullable();
            $table->unsignedBigInteger('class_model_id')->nullable();

            $table->string('name');
            $table->text('description')->nullable();

            // Her grupta sadece 1 pinli mesaj için:
            // FK koymuyoruz, sadece id tutacağız (dairesel ilişki karmaşık olmasın diye)
            $table->unsignedBigInteger('pinned_message_id')->nullable();

            // Görünürlük: public / restricted / hidden
            $table->string('visibility')->default('restricted');

            $table->unsignedBigInteger('created_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexler
            $table->index(['type', 'school_id']);
            $table->index('class_model_id');
            $table->index('visibility');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('groups');
    }
};
