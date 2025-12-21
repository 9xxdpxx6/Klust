<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SkillLevelUpNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private string $skillName,
        private int $newLevel
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
            ->subject('Повышение уровня навыка!')
            ->line("Ваш навык {$this->skillName} достиг уровня {$this->newLevel}!")
            ->action('Посмотреть навыки', url('/student/skills'))
            ->line('Продолжайте развиваться!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'title' => 'Повышение уровня навыка!',
            'message' => "Ваш навык {$this->skillName} достиг уровня {$this->newLevel}!",
            'type' => 'skill_level_up',
            'link' => '/student/skills',
            'icon' => 'pi-chart-line',
            'action_text' => 'Посмотреть навыки',
            'skill_name' => $this->skillName,
            'new_level' => $this->newLevel,
        ];
    }
}

