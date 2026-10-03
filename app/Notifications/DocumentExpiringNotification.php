<?php

namespace App\Notifications;

use App\Models\UserDocument;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DocumentExpiringNotification extends Notification
{
    public function __construct(private readonly UserDocument $document) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Kończy się ważność dokumentu: '.$this->document->type->name)
            ->line('Dokument "'.$this->document->type->name.'" jest ważny do '.$this->document->valid_until->format('d.m.Y').'.')
            ->line('Przynieś proszę aktualny dokument do kierownika.')
            ->action('Moje dokumenty', route('documents.index'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'document_id' => $this->document->id,
            'type' => $this->document->type->name,
            'valid_until' => $this->document->valid_until->toDateString(),
        ];
    }
}
