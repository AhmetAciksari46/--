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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('group_id');
            $table->unsignedBigInteger('user_id');

            $table->text('content')->nullable(); // sadece dosya içeren mesajlara izin vermek için nullable

            // normal / announcement / homework / file / image / video
            $table->string('message_type')->default('normal');

            // active / deleted gibi
            $table->string('status')->default('active');

            $table->timestamp('edited_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('group_id')->references('id')->on('groups')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->index(['group_id', 'created_at']);
            $table->index('user_id');
            $table->index('message_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
