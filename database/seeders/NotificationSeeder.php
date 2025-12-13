<?php

namespace Database\Seeders;

use App\Models\AppNotification;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        // 400 уведомлений с разнообразными датами (от 6 месяцев назад до сейчас)
        // Не все пользователи получают одинаковое количество уведомлений
        $totalNotifications = 400;
        $notificationsCreated = 0;

        while ($notificationsCreated < $totalNotifications) {
            $user = $users->random();
            
            // Количество уведомлений для пользователя (0-8, но чаще 1-3)
            $notificationsCount = fake()->numberBetween(0, 8);
            if ($notificationsCount === 0 && fake()->boolean(70)) {
                continue; // 70% шанс пропустить пользователя без уведомлений
            }
            
            // Ограничиваем, чтобы не превысить общее количество
            $remaining = $totalNotifications - $notificationsCreated;
            $notificationsCount = min($notificationsCount, $remaining);

            for ($i = 0; $i < $notificationsCount; $i++) {
                // Дата уведомления (от 6 месяцев назад до сейчас)
                $createdAt = fake()->dateTimeBetween('-6 months', 'now');
                
                // Типы уведомлений с весами
                $types = [
                    'info' => 30,
                    'success' => 25,
                    'warning' => 20,
                    'error' => 10,
                    'application_submitted' => 5,
                    'application_approved' => 5,
                    'application_rejected' => 5,
                ];
                
                $type = fake()->randomElement(array_merge(
                    array_fill(0, $types['info'], 'info'),
                    array_fill(0, $types['success'], 'success'),
                    array_fill(0, $types['warning'], 'warning'),
                    array_fill(0, $types['error'], 'error'),
                    array_fill(0, $types['application_submitted'], 'application_submitted'),
                    array_fill(0, $types['application_approved'], 'application_approved'),
                    array_fill(0, $types['application_rejected'], 'application_rejected')
                ));

                // Текст уведомления зависит от типа
                $message = match ($type) {
                    'info' => fake()->randomElement([
                        'Новый кейс доступен для подачи заявок',
                        'Обновлена информация о кейсе',
                        'Добавлен новый симулятор',
                    ]),
                    'success' => fake()->randomElement([
                        'Ваша заявка принята!',
                        'Поздравляем с получением бейджа!',
                        'Симулятор успешно завершен',
                    ]),
                    'warning' => fake()->randomElement([
                        'Срок подачи заявки истекает',
                        'Не забудьте завершить симулятор',
                        'Требуется обновление профиля',
                    ]),
                    'error' => fake()->randomElement([
                        'Заявка отклонена',
                        'Ошибка при обработке запроса',
                    ]),
                    'application_submitted' => 'Ваша заявка на кейс отправлена',
                    'application_approved' => 'Ваша заявка на кейс одобрена',
                    'application_rejected' => 'Ваша заявка на кейс отклонена',
                    default => fake()->sentence(),
                };

                // Заголовок зависит от типа
                $title = match ($type) {
                    'info' => fake()->randomElement([
                        'Новый кейс доступен',
                        'Обновление информации',
                        'Новый симулятор',
                        'Важная информация',
                    ]),
                    'success' => fake()->randomElement([
                        'Заявка принята',
                        'Поздравляем!',
                        'Успешное завершение',
                        'Достижение разблокировано',
                    ]),
                    'warning' => fake()->randomElement([
                        'Внимание!',
                        'Срок истекает',
                        'Требуется действие',
                        'Напоминание',
                    ]),
                    'error' => fake()->randomElement([
                        'Заявка отклонена',
                        'Ошибка',
                        'Требуется внимание',
                    ]),
                    'application_submitted' => 'Заявка отправлена',
                    'application_approved' => 'Заявка одобрена',
                    'application_rejected' => 'Заявка отклонена',
                    default => 'Уведомление',
                };

                AppNotification::create([
                    'user_id' => $user->id,
                    'type' => $type,
                    'title' => $title,
                    'message' => $message,
                    'link' => fake()->randomElement(['/cases', '/profile', '/learning', '/']),
                    'icon' => fake()->randomElement(['fa-bell', 'fa-info-circle', 'fa-check-circle', 'fa-exclamation-triangle']),
                    'action_text' => fake()->randomElement(['Перейти', 'Открыть', 'Просмотреть', 'Узнать больше']),
                    'is_read' => fake()->boolean(60), // 60% прочитаны
                    'created_at' => $createdAt,
                    'updated_at' => fake()->boolean(60) && fake()->boolean(60) 
                        ? fake()->dateTimeBetween($createdAt, 'now') 
                        : $createdAt,
                ]);
                
                $notificationsCreated++;
                
                if ($notificationsCreated >= $totalNotifications) {
                    break 2;
                }
            }
        }
    }
}
