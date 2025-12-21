<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\CaseApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApplicationRejectedNotification extends Notification implements ShouldQueue
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
            ->subject('Заявка отклонена')
            ->line("Ваша заявка на кейс '{$this->application->case->title}' была отклонена.")
            ->line("Причина: {$this->application->rejection_reason ?? 'Не указана'}")
            ->action('Подать заявку на другой кейс', url('/student/cases'))
            ->line('Спасибо за участие. Попробуйте подать заявку на другой кейс!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'title' => 'Заявка отклонена',
            'message' => "Ваша заявка на кейс '{$this->application->case->title}' была отклонена",
            'type' => 'application_rejected',
            'link' => '/student/cases',
            'icon' => 'pi-times-circle',
            'action_text' => 'Просмотреть кейс',
            'case_id' => $this->application->case->id,
            'application_id' => $this->application->id,
            'rejection_reason' => $this->application->rejection_reason,
        ];
    }

    /**
     * Get the broadcast representation of the notification.
     */
    public function toBroadcast($notifiable): array
    {
        return [
            'id' => $this->id,
            'type' => 'application_rejected',
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