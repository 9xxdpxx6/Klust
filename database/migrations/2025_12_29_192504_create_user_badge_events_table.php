<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_badge_events', function (Blueprint $table) {
            $table->id();

            // Пользователь, получивший ачивку
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            // Ачивка
            $table->foreignId('badge_id')
                ->constrained()
                ->cascadeOnDelete();

            // Причина выдачи (enum-like string)
            $table->string('reason_type');

            // Контекст (опционально, например кейс)
            $table->foreignId('case_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            // Дополнительное текстовое пояснение
            $table->text('description')->nullable();

            // Уровень ачивки на момент события
            $table->integer('level')->nullable();

            // Время выдачи
            $table->timestamp('earned_at')->useCurrent();
            $table->timestamps();

            // Быстрый поиск по пользователю и ачивке
            $table->index(['user_id', 'badge_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_badge_events');
    }
};

