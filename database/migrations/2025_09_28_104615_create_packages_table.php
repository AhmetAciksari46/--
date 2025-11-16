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
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('duration_days'); // ör: 365 gün
            $table->decimal('price', 10, 2);
            $table->enum('type', ['school', 'student', 'other'])->default('school');
            $table->boolean('is_active')->default(true);
            $table->boolean('has_homework_module')->default(false);
            $table->boolean('has_schedule_module')->default(false);
            $table->unsignedInteger('week_count');

            $table->boolean('has_exam_module')->default(false);
            $table->boolean('has_chat_module')->default(false);
            $table->boolean('has_analytics_module')->default(false);
            $table->boolean('has_certificate_module')->default(false);
            $table->boolean('is_visible')->default(true);
            $table->boolean('is_trial')->default(false);
            $table->integer('trial_days')->default(0);
            $table->integer('sort_order')->nullable();
            $table->string('img_path')->nullable();
            $table->boolean('is_sequential_content_required')->default(false)
                ->comment('Önceki ödev tamamlanmadan sonraki içerik açılmasın kuralı.');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};
