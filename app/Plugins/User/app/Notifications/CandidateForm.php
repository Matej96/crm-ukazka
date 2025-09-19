<?php

namespace App\Plugins\User\app\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CandidateForm extends Notification
{
    use Queueable;

    public string $subject;

    /**
     * Create a new notification instance.
     *
     * @param array $record_ids
     * @return void
     */
    public function __construct(public \App\Plugins\User\app\Models\Candidate $candidate)
    {
        $this->subject = 'Formulár pre spoluprácu';
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)->subject($this->subject)
            ->view('plugin.User::mails.candidate_form', [
                'candidate' => $this->candidate,
            ]);
    }
}
