<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('simulator_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('simulator_id')->constrained()->cascadeOnDelete();
            $table->json('state')->nullable();
            $table->integer('score')->default(0);
            $table->integer('time_spent')->default(0);
            $table->boolean('is_completed')->default(false);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'simulator_id']);
            $table->index('is_completed');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('simulator_sessions');
    }
};
