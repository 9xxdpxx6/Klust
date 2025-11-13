<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\CaseApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApplicationApprovedNotification extends Notification implements ShouldQueue
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
        return ['database', 'broadcast'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Заявка одобрена')
            ->line("Ваша заявка на кейс '{$this->application->case->title}' была одобрена!")
            ->line("Статус: {$this->application->status->name}")
            ->action('Перейти к команде', url("/student/team/{$this->application->id}"))
            ->line('Поздравляем и удачи в выполнении кейса!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'title' => 'Заявка одобрена',
            'message' => "Ваша заявка на кейс '{$this->application->case->title}' была успешно одобрена",
            'link' => "/student/team/{$this->application->id}",
            'case_id' => $this->application->case->id,
            'application_id' => $this->application->id,
            'status' => $this->application->status->name,
        ];
    }

    /**
     * Get the broadcast representation of the notification.
     */
    public function toBroadcast($notifiable): array
    {
        return [
            'id' => $this->id,
            'type' => 'application_approved',
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