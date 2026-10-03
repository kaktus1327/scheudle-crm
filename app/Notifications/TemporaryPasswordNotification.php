<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TemporaryPasswordNotification extends Notification
{
    public function __construct(
        #[\SensitiveParameter] private readonly string $password,
        private readonly bool $isReset = false,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->isReset ? 'Twoje hasło zostało zresetowane' : 'Witaj w grafiku kina')
            ->greeting('Cześć '.$notifiable->name.'!')
            ->line($this->isReset
                ? 'Kierownik zresetował Twoje hasło. Poniżej znajdziesz nowe hasło jednorazowe.'
                : 'Kierownik założył Ci konto w aplikacji z grafikiem. Poniżej znajdziesz hasło jednorazowe.')
            ->line('Hasło: **'.$this->password.'**')
            ->line('Po pierwszym zalogowaniu aplikacja poprosi Cię o ustawienie własnego hasła.')
            ->action('Zaloguj się', route('login'));
    }
}
