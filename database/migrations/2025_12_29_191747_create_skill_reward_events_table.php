<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('skill_reward_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('case_id')->constrained()->cascadeOnDelete();
            $table->foreignId('skill_id')->constrained()->cascadeOnDelete();
            $table->foreignId('difficulty_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('points_awarded');
            $table->timestamps();

            $table->index(['user_id', 'skill_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('skill_reward_events');
    }
};

