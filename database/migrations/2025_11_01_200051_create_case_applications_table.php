<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('case_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained()->cascadeOnDelete();
            $table->foreignId('leader_id')->constrained('users')->cascadeOnDelete();
            $table->text('motivation');
            $table->foreignId('status_id')->default(1)->constrained('application_statuses');
            $table->text('rejection_reason')->nullable();
            $table->text('partner_comment')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('submitted_at')->useCurrent();
            $table->timestamps();

            $table->index(['case_id', 'status_id']);
            $table->index('leader_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('case_applications');
    }
};

