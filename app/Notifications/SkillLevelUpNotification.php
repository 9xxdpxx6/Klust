<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SkillLevelUpNotification extends Notification
{
    use Queueable;

    public function __construct(
        private string $skillName,
        private int $newLevel
    ) {}

    /**
     * Get the notification's delivery channels.
     * Only database — in-app notifications (no email spam for skill levels).
     */
    public function via($notifiable): array
    {
        return ['database'];
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
