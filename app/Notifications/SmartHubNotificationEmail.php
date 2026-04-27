<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SmartHubNotificationEmail extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly string $subjectLine,
        private readonly string $headline,
        private readonly string $body,
        private readonly ?string $actionText = null,
        private readonly ?string $actionUrl = null,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->subjectLine)
            ->markdown('emails.notifications', [
                'user' => $notifiable,
                'headline' => $this->headline,
                'body' => $this->body,
                'actionText' => $this->actionText,
                'actionUrl' => $this->actionUrl,
            ]);
    }
}
