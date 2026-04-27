<?php

namespace App\Notifications;

use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProjectReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Project $project,
        private readonly string $message,
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
            ->subject('Project Reminder: '.$this->project->name)
            ->markdown('emails.project-reminder', [
                'user' => $notifiable,
                'project' => $this->project,
                'messageText' => $this->message,
                'projectUrl' => $this->actionUrl ?: route('projects.show', $this->project),
            ]);
    }
}
