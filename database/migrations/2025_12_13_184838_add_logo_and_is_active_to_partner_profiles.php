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
        // Добавить поля logo и is_active в partner_profiles, если их нет
        if (Schema::hasTable('partner_profiles')) {
            Schema::table('partner_profiles', function (Blueprint $table) {
                if (!Schema::hasColumn('partner_profiles', 'logo')) {
                    $table->string('logo')->nullable()->after('contact_phone');
                }
                if (!Schema::hasColumn('partner_profiles', 'is_active')) {
                    $table->boolean('is_active')->default(true)->after('logo');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('partner_profiles')) {
            Schema::table('partner_profiles', function (Blueprint $table) {
                if (Schema::hasColumn('partner_profiles', 'logo')) {
                    $table->dropColumn('logo');
                }
                if (Schema::hasColumn('partner_profiles', 'is_active')) {
                    $table->dropColumn('is_active');
                }
            });
        }
    }
};
