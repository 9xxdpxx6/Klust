<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description');
            $table->foreignId('simulator_id')->nullable()->constrained();
            $table->date('deadline');
            $table->text('reward');
            $table->integer('required_team_size')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['partner_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cases');
    }
};

