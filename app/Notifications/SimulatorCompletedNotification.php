<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SimulatorCompletedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private string $simulatorName,
        private int $pointsEarned
    ) {}

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Симулятор пройден!')
            ->line("Вы успешно завершили симулятор \"{$this->simulatorName}\" и получили {$this->pointsEarned} очков")
            ->action('Просмотреть симуляторы', url('/student/simulators'))
            ->line('Продолжайте обучение!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'title' => 'Симулятор пройден!',
            'message' => "Вы успешно завершили симулятор \"{$this->simulatorName}\" и получили {$this->pointsEarned} очков",
            'type' => 'simulator_completed',
            'link' => '/student/simulators',
            'icon' => 'pi-trophy',
            'action_text' => 'Просмотреть',
            'simulator_name' => $this->simulatorName,
            'points_earned' => $this->pointsEarned,
        ];
    }
}

