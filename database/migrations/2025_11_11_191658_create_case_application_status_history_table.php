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
        Schema::create('case_application_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_application_id')->constrained('case_applications')->onDelete('cascade');
            $table->foreignId('old_status_id')->nullable()->constrained('application_statuses');
            $table->foreignId('new_status_id')->constrained('application_statuses');
            $table->text('comment')->nullable();
            $table->foreignId('changed_by')->constrained('users')->onDelete('cascade');
            $table->timestamp('changed_at');
            $table->timestamps();

            $table->index('case_application_id');
            $table->index('new_status_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('case_application_status_history');
    }
};
