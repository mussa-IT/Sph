<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BrandedResetPasswordNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly string $token)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $resetUrl = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        return (new MailMessage)
            ->subject('Reset your Smart Project Hub password')
            ->markdown('emails.password-reset', [
                'user' => $notifiable,
                'resetUrl' => $resetUrl,
                'expireMinutes' => (int) config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60),
            ]);
    }
}
