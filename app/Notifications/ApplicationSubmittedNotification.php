<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\CaseApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApplicationSubmittedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private CaseApplication $application
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
            ->subject('Новая заявка на кейс')
            ->line("На ваш кейс '{$this->application->case->title}' подана новая заявка.")
            ->line("Лидер команды: {$this->application->leader->name}")
            ->line("Количество участников: {$this->application->team_size}")
            ->action('Просмотреть заявку', url("/partner/cases/{$this->application->case->id}/applications"))
            ->line('Спасибо за использование нашего сервиса!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'title' => 'Новая заявка на кейс',
            'message' => "На ваш кейс '{$this->application->case->title}' подана новая заявка от {$this->application->leader->name}",
            'type' => 'new_application',
            'link' => "/partner/cases/{$this->application->case->id}/applications",
            'icon' => 'pi-inbox',
            'action_text' => 'Просмотреть',
            'case_id' => $this->application->case->id,
            'application_id' => $this->application->id,
            'leader_name' => $this->application->leader->name,
        ];
    }

    /**
     * Get the broadcast representation of the notification.
     */
    public function toBroadcast($notifiable): array
    {
        return [
            'id' => $this->id,
            'type' => 'application_submitted',
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
