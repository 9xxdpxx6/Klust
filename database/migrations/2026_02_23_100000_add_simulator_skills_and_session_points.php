<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pivot table: simulator ↔ skills (direct link, independent of cases)
        Schema::create('simulator_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('simulator_id')->constrained()->cascadeOnDelete();
            $table->foreignId('skill_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['simulator_id', 'skill_id']);
        });

        // Add missing columns to simulator_sessions
        Schema::table('simulator_sessions', function (Blueprint $table) {
            $table->unsignedInteger('points_earned')->default(0)->after('time_spent');
            $table->json('answers')->nullable()->after('points_earned');
        });
    }

    public function down(): void
    {
        Schema::table('simulator_sessions', function (Blueprint $table) {
            $table->dropColumn(['points_earned', 'answers']);
        });

        Schema::dropIfExists('simulator_skills');
    }
};
