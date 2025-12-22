<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Mail\ApplicationStatusChangedMail;
use App\Models\CaseApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ApplicationStatusChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private CaseApplication $application,
        private string $oldStatusName,
        private string $newStatusName,
        private ?string $comment = null
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
    public function toMail($notifiable): ApplicationStatusChangedMail
    {
        $statusLabels = [
            'pending' => 'На рассмотрении',
            'accepted' => 'Принята',
            'rejected' => 'Отклонена',
        ];

        $newStatusLabel = $statusLabels[$this->newStatusName] ?? $this->newStatusName;
        $oldStatusLabel = $statusLabels[$this->oldStatusName] ?? $this->oldStatusName;

        // Определяем URL и текст действия в зависимости от статуса
        $actionUrl = match ($this->newStatusName) {
            'accepted' => url("/student/team/{$this->application->id}"),
            'pending' => url("/student/cases/{$this->application->case->id}"),
            'rejected' => url('/student/cases'),
            default => url('/student/cases'),
        };

        $actionText = match ($this->newStatusName) {
            'accepted' => 'Перейти к команде',
            'pending' => 'Просмотреть заявку',
            'rejected' => 'Подать заявку на другой кейс',
            default => 'Просмотреть',
        };

        // Получаем email через routeNotificationForMail если он есть, иначе напрямую
        $recipientEmail = null;
        $recipientName = null;

        if (method_exists($notifiable, 'routeNotificationForMail')) {
            $routeResult = $notifiable->routeNotificationForMail($this);
            if (is_array($routeResult)) {
                // Результат: [email => name]
                $recipientEmail = array_key_first($routeResult);
                $recipientName = $routeResult[$recipientEmail];
            } elseif (is_string($routeResult)) {
                // Результат: только email
                $recipientEmail = $routeResult;
                $recipientName = $notifiable->name ?? null;
            }
        }

        // Если routeNotificationForMail вернул null или не существует, проверяем напрямую
        if (!$recipientEmail) {
            $notifiableEmail = $notifiable->email ?? null;
            
            // Проверяем валидность email
            if ($notifiableEmail && filter_var($notifiableEmail, FILTER_VALIDATE_EMAIL)) {
                $recipientEmail = $notifiableEmail;
                $recipientName = $notifiable->name ?? null;
            } else {
                // Email невалидный, используем email из leader как fallback
                $recipientEmail = $this->application->leader->email;
                $recipientName = $this->application->leader->name;
            }
        }

        // Создаем Mailable и устанавливаем получателя через ->to() метод
        $mailable = new ApplicationStatusChangedMail(
            application: $this->application,
            oldStatusName: $oldStatusLabel,
            newStatusName: $newStatusLabel,
            comment: $this->comment,
            actionUrl: $actionUrl,
            actionText: $actionText,
            statusKey: $this->newStatusName // Передаем оригинальный ключ статуса для цветов
        );

        // Устанавливаем получателя с проверкой валидности
        if ($recipientName) {
            return $mailable->to($recipientEmail, $recipientName);
        }

        return $mailable->to($recipientEmail);
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        $statusLabels = [
            'pending' => 'На рассмотрении',
            'accepted' => 'Принята',
            'rejected' => 'Отклонена',
        ];

        $statusIcons = [
            'pending' => 'pi-clock',
            'accepted' => 'pi-check-circle',
            'rejected' => 'pi-times-circle',
        ];

        $statusLinks = [
            'pending' => "/student/cases/{$this->application->case->id}",
            'accepted' => "/student/team/{$this->application->id}",
            'rejected' => '/student/cases',
        ];

        $statusActionTexts = [
            'pending' => 'Просмотреть заявку',
            'accepted' => 'Перейти к команде',
            'rejected' => 'Просмотреть кейсы',
        ];

        $newStatusLabel = $statusLabels[$this->newStatusName] ?? $this->newStatusName;
        $oldStatusLabel = $statusLabels[$this->oldStatusName] ?? $this->oldStatusName;

        return [
            'title' => "Статус заявки изменен: {$newStatusLabel}",
            'message' => "Статус вашей заявки на кейс '{$this->application->case->title}' был изменен с '{$oldStatusLabel}' на '{$newStatusLabel}'",
            'type' => 'application_status_changed',
            'link' => $statusLinks[$this->newStatusName] ?? '/student/cases',
            'icon' => $statusIcons[$this->newStatusName] ?? 'pi-info-circle',
            'action_text' => $statusActionTexts[$this->newStatusName] ?? 'Просмотреть',
            'case_id' => $this->application->case->id,
            'application_id' => $this->application->id,
            'old_status' => $this->oldStatusName,
            'new_status' => $this->newStatusName,
            'comment' => $this->comment,
        ];
    }

    /**
     * Get the broadcast representation of the notification.
     */
    public function toBroadcast($notifiable): array
    {
        return [
            'id' => $this->id,
            'type' => 'application_status_changed',
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

