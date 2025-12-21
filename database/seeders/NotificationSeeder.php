<?php

namespace Database\Seeders;

use App\Models\User;
use App\Notifications\GenericNotification;
use Illuminate\Database\Seeder;

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
                        'Поздравляем с получением достижения!',
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

                $notification = new GenericNotification(
                    $title,
                    $message,
                    $type,
                    fake()->randomElement(['/cases', '/profile', '/learning', '/']),
                    fake()->randomElement(['pi-bell', 'pi-info-circle', 'pi-check-circle', 'pi-exclamation-triangle']),
                    fake()->randomElement(['Перейти', 'Открыть', 'Просмотреть', 'Узнать больше'])
                );

                $user->notify($notification);

                // Устанавливаем created_at вручную для реалистичности
                $dbNotification = $user->notifications()->latest()->first();
                if ($dbNotification) {
                    $dbNotification->created_at = $createdAt;
                    $dbNotification->updated_at = fake()->boolean(60) && fake()->boolean(60)
                        ? fake()->dateTimeBetween($createdAt, 'now')
                        : $createdAt;
                    // Отмечаем как прочитанное, если нужно
                    if (fake()->boolean(60)) {
                        $dbNotification->read_at = fake()->dateTimeBetween($createdAt, 'now');
                    }
                    $dbNotification->save();
                }
                
                $notificationsCreated++;
                
                if ($notificationsCreated >= $totalNotifications) {
                    break 2;
                }
            }
        }

        // Создаем много уведомлений для тестовых пользователей
        $testUsers = User::whereIn('email', [
            'asd@asd.asd', // Админ
            'qwe@qwe.qwe', // Суперпользователь
            'zxc@zxc.zxc', // Студент
            'wer@wer.wer', // Партнер
            'sdf@sdf.sdf', // Преподаватель
        ])->get();

        foreach ($testUsers as $testUser) {
            // 50 уведомлений для каждого тестового пользователя
            for ($i = 0; $i < 50; $i++) {
                $createdAt = fake()->dateTimeBetween('-6 months', 'now');
                
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

                $message = match ($type) {
                    'info' => fake()->randomElement([
                        'Новый кейс доступен для подачи заявок',
                        'Обновлена информация о кейсе',
                        'Добавлен новый симулятор',
                    ]),
                    'success' => fake()->randomElement([
                        'Ваша заявка принята!',
                        'Поздравляем с получением достижения!',
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

                $notification = new GenericNotification(
                    $title,
                    $message,
                    $type,
                    fake()->randomElement(['/cases', '/profile', '/learning', '/']),
                    fake()->randomElement(['pi-bell', 'pi-info-circle', 'pi-check-circle', 'pi-exclamation-triangle']),
                    fake()->randomElement(['Перейти', 'Открыть', 'Просмотреть', 'Узнать больше'])
                );

                $testUser->notify($notification);

                // Устанавливаем created_at вручную для реалистичности
                $dbNotification = $testUser->notifications()->latest()->first();
                if ($dbNotification) {
                    $dbNotification->created_at = $createdAt;
                    $dbNotification->updated_at = fake()->boolean(60) && fake()->boolean(60)
                        ? fake()->dateTimeBetween($createdAt, 'now')
                        : $createdAt;
                    // Отмечаем как прочитанное, если нужно
                    if (fake()->boolean(60)) {
                        $dbNotification->read_at = fake()->dateTimeBetween($createdAt, 'now');
                    }
                    $dbNotification->save();
                }
            }
        }
    }
}
