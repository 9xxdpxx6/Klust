<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Обновить таблицу cases: заменить partner_id на user_id
        if (Schema::hasTable('cases')) {
            // Проверяем, есть ли колонка partner_id
            if (Schema::hasColumn('cases', 'partner_id') && !Schema::hasColumn('cases', 'user_id')) {
                // Добавляем колонку user_id
                Schema::table('cases', function (Blueprint $table) {
                    $table->foreignId('user_id')->nullable()->after('id');
                });

                // Переносим данные из partner_id в user_id через таблицу partners (если она существует)
                if (Schema::hasTable('partners')) {
                    DB::statement('
                        UPDATE cases 
                        INNER JOIN partners ON cases.partner_id = partners.id 
                        SET cases.user_id = partners.user_id 
                        WHERE cases.partner_id IS NOT NULL AND partners.user_id IS NOT NULL
                    ');
                }

                // Удаляем старый индекс и foreign key
                Schema::table('cases', function (Blueprint $table) {
                    $table->dropForeign(['partner_id']);
                    $table->dropIndex(['partner_id', 'status']);
                });

                // Удаляем колонку partner_id
                Schema::table('cases', function (Blueprint $table) {
                    $table->dropColumn('partner_id');
                });

                // Делаем user_id обязательным и добавляем foreign key и индекс
                Schema::table('cases', function (Blueprint $table) {
                    $table->foreignId('user_id')->nullable(false)->change();
                    $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
                    $table->index(['user_id', 'status']);
                });
            } elseif (Schema::hasColumn('cases', 'partner_id') && Schema::hasColumn('cases', 'user_id')) {
                // Если обе колонки существуют, просто удаляем partner_id
                Schema::table('cases', function (Blueprint $table) {
                    if (Schema::hasColumn('cases', 'partner_id')) {
                        try {
                            $table->dropForeign(['partner_id']);
                        } catch (\Exception $e) {
                            // Игнорируем ошибку, если foreign key не существует
                        }
                        $table->dropColumn('partner_id');
                    }
                });
            }
        }

        // Обновить таблицу simulators: заменить partner_id на user_id
        if (Schema::hasTable('simulators')) {
            // Проверяем, есть ли колонка partner_id
            if (Schema::hasColumn('simulators', 'partner_id') && !Schema::hasColumn('simulators', 'user_id')) {
                // Добавляем колонку user_id
                Schema::table('simulators', function (Blueprint $table) {
                    $table->foreignId('user_id')->nullable()->after('id');
                });

                // Переносим данные из partner_id в user_id через таблицу partners (если она существует)
                if (Schema::hasTable('partners')) {
                    DB::statement('
                        UPDATE simulators 
                        INNER JOIN partners ON simulators.partner_id = partners.id 
                        SET simulators.user_id = partners.user_id 
                        WHERE simulators.partner_id IS NOT NULL AND partners.user_id IS NOT NULL
                    ');
                }

                // Удаляем старый индекс и foreign key
                Schema::table('simulators', function (Blueprint $table) {
                    try {
                        $table->dropForeign(['partner_id']);
                    } catch (\Exception $e) {
                        // Игнорируем ошибку, если foreign key не существует
                    }
                    $table->dropIndex(['partner_id', 'is_active']);
                });

                // Удаляем колонку partner_id
                Schema::table('simulators', function (Blueprint $table) {
                    $table->dropColumn('partner_id');
                });

                // Делаем user_id обязательным и добавляем foreign key и индекс
                Schema::table('simulators', function (Blueprint $table) {
                    $table->foreignId('user_id')->nullable(false)->change();
                    $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
                    $table->index(['user_id', 'is_active']);
                });
            } elseif (Schema::hasColumn('simulators', 'partner_id') && Schema::hasColumn('simulators', 'user_id')) {
                // Если обе колонки существуют, просто удаляем partner_id
                Schema::table('simulators', function (Blueprint $table) {
                    if (Schema::hasColumn('simulators', 'partner_id')) {
                        try {
                            $table->dropForeign(['partner_id']);
                        } catch (\Exception $e) {
                            // Игнорируем ошибку, если foreign key не существует
                        }
                        $table->dropColumn('partner_id');
                    }
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Откат изменений (не рекомендуется, но на всякий случай)
        if (Schema::hasTable('cases') && Schema::hasColumn('cases', 'user_id')) {
            Schema::table('cases', function (Blueprint $table) {
                $table->foreignId('partner_id')->nullable()->after('id');
            });

            // Перенос данных обратно (требует наличия таблицы partners)
            if (Schema::hasTable('partners')) {
                DB::statement('
                    UPDATE cases 
                    INNER JOIN partners ON cases.user_id = partners.user_id 
                    SET cases.partner_id = partners.id 
                    WHERE cases.user_id IS NOT NULL
                ');
            }

            Schema::table('cases', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
                $table->dropIndex(['user_id', 'status']);
                $table->dropColumn('user_id');
                $table->foreign('partner_id')->references('id')->on('partners')->cascadeOnDelete();
                $table->index(['partner_id', 'status']);
            });
        }

        if (Schema::hasTable('simulators') && Schema::hasColumn('simulators', 'user_id')) {
            Schema::table('simulators', function (Blueprint $table) {
                $table->foreignId('partner_id')->nullable()->after('id');
            });

            // Перенос данных обратно (требует наличия таблицы partners)
            if (Schema::hasTable('partners')) {
                DB::statement('
                    UPDATE simulators 
                    INNER JOIN partners ON simulators.user_id = partners.user_id 
                    SET simulators.partner_id = partners.id 
                    WHERE simulators.user_id IS NOT NULL
                ');
            }

            Schema::table('simulators', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
                $table->dropIndex(['user_id', 'is_active']);
                $table->dropColumn('user_id');
                $table->foreign('partner_id')->references('id')->on('partners')->cascadeOnDelete();
                $table->index(['partner_id', 'is_active']);
            });
        }
    }
};
