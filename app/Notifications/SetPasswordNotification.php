<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SetPasswordNotification extends Notification
{
    use Queueable;

    private $user;
    private $token;

    /**
     * Create a new notification instance.
     */
    public function __construct($user, $token)
    {
        $this->user = $user;
        $this->token = $token;
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
        return (new MailMessage)
            ->subject('Set Your Password - Secure Access')
            ->greeting('Hello ' . $this->user->firstname . '!')
            ->line('Your account has been created by the administrator.')
            ->line('Please use the following code to set your password:')
            ->line('**' . $this->token . '**')
            ->line('This code will expire in 24 hours.')
            ->line('If you did not expect this email, please contact support.')
            ->salutation('Best regards, Secure Access Team');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}

