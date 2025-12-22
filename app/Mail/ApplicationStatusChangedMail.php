<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\CaseApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApplicationStatusChangedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public CaseApplication $application,
        public string $oldStatusName,
        public string $newStatusName,
        public ?string $comment = null,
        public string $actionUrl = '',
        public string $actionText = 'Просмотреть',
        public ?string $recipientEmail = null,
        public ?string $recipientName = null,
        public string $statusKey = 'pending' // Оригинальный ключ статуса для определения цветов
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Статус заявки изменен: {$this->newStatusName}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.application-status-changed',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}

