<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\CaseModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewCasePublishedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private CaseModel $case
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
            ->subject('Новый кейс опубликован')
            ->line("Новый кейс '{$this->case->title}' опубликован!")
            ->line("Компания: {$this->case->partner->company_name}")
            ->line("Требуемые навыки: " . $this->case->required_skills->pluck('name')->join(', '))
            ->line("Дедлайн: {$this->case->deadline->format('d.m.Y')}")
            ->action('Просмотреть кейс', url("/student/cases/{$this->case->id}"))
            ->line('Подайте заявку, если кейс вам интересен!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'title' => 'Новый кейс опубликован',
            'message' => "Новый кейс '{$this->case->title}' опубликован компанией {$this->case->partner->company_name}",
            'link' => "/student/cases/{$this->case->id}",
            'case_id' => $this->case->id,
            'partner_name' => $this->case->partner->company_name,
            'deadline' => $this->case->deadline->format('d.m.Y'),
            'required_skills_count' => $this->case->required_skills->count(),
        ];
    }

    /**
     * Get the broadcast representation of the notification.
     */
    public function toBroadcast($notifiable): array
    {
        return [
            'id' => $this->id,
            'type' => 'new_case_published',
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