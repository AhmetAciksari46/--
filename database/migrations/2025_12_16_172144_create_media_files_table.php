<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('media_files', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('type', 20); // image, video, audio, document
            $table->string('file_name')->nullable();
            $table->string('file_path');
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size');

            $table->timestamps();
            $table->softDeletes();

            $table->index(['type']);
            $table->index(['user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_files');
    }
};
