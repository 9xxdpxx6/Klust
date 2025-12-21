<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Badge;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BadgeEarnedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private Badge $badge
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
            ->subject('Новое достижение получено!')
            ->line("Поздравляем! Вы получили новое достижение: {$this->badge->name}")
            ->line("Описание: {$this->badge->description}")
            ->action('Просмотреть достижения', url('/student/badges'))
            ->line('Продолжайте в том же духе!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'title' => 'Новое достижение получено!',
            'message' => "Поздравляем! Вы получили новое достижение: {$this->badge->name}",
            'type' => 'badge_earned',
            'link' => '/student/badges',
            'icon' => 'pi-star',
            'action_text' => 'Посмотреть достижения',
            'badge_id' => $this->badge->id,
            'badge_name' => $this->badge->name,
            'badge_description' => $this->badge->description,
            'badge_icon' => $this->badge->icon,
        ];
    }

    /**
     * Get the broadcast representation of the notification.
     */
    public function toBroadcast($notifiable): array
    {
        return [
            'id' => $this->id,
            'type' => 'badge_earned',
            'data' => $this->toArray($notifiable),
            'created_at' => $this->carbonDate(),
        ];
    }

    /**
     * Format the created_at date for broadcast.
     */
    private function carbonDate(): string
    {
        return now()->toIso8601String();
    }
}
