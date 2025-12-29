<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('skill_reward_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('skill_id')->constrained()->cascadeOnDelete();
            $table->foreignId('difficulty_id')->constrained()->cascadeOnDelete();
            $table->integer('points');
            $table->timestamps();

            $table->unique(['skill_id', 'difficulty_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('skill_reward_rules');
    }
};

