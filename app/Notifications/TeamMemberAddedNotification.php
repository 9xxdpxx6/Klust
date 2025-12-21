<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\CaseApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TeamMemberAddedNotification extends Notification implements ShouldQueue
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
        $leaderName = $this->application->leader->name;
        $caseName = $this->application->case->title;

        return (new MailMessage)
            ->subject('Вы добавлены в команду')
            ->line("{$leaderName} добавил(а) вас в команду для кейса \"{$caseName}\"")
            ->action('Перейти к команде', url("/student/team/{$this->application->id}"))
            ->line('Удачи в выполнении кейса!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        $leaderName = $this->application->leader->name;
        $caseName = $this->application->case->title;

        return [
            'title' => 'Вы добавлены в команду',
            'message' => "{$leaderName} добавил(а) вас в команду для кейса \"{$caseName}\"",
            'type' => 'team_addition',
            'link' => "/student/team/{$this->application->id}",
            'icon' => 'pi-users',
            'action_text' => 'Перейти к команде',
            'application_id' => $this->application->id,
            'case_id' => $this->application->case->id,
            'leader_name' => $leaderName,
            'case_name' => $caseName,
        ];
    }
}

