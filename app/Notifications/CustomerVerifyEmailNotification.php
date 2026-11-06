<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CustomerVerifyEmailNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly string $verificationUrl)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('Verify your EntrepreneurWiki email address')
            ->view('emails.customer.verify-email', [
                'customer' => $notifiable,
                'verificationUrl' => $this->verificationUrl,
            ]);
    }
}
