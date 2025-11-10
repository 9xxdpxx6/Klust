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
        private User $user,
        private Badge $badge
    ) {}

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Новый бейдж earned!')
            ->line("Поздравляем! Вы получили новый бейдж: {$this->badge->name}")
            ->line("Описание: {$this->badge->description}")
            ->action('Просмотреть бейджи', url('/student/badges'))
            ->line('Продолжайте в том же духе!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'title' => 'Новый бейдж earned!',
            'message' => "Поздравляем! Вы получили новый бейдж: {$this->badge->name}",
            'link' => '/student/badges',
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