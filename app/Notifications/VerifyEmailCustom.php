<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyEmailCustom extends VerifyEmail
{
    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via($notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject(__('Verify Your Email Address'))
            ->greeting('Hello, ' . ($notifiable->name ?? 'User') . '!')
            ->line('Thank you for signing up! To complete your registration, please verify your email address by clicking the button below.')
            ->action('Verify Email', $this->verificationUrl($notifiable))
            ->line('If you did not request this verification, you can safely ignore this email.')
            ->salutation('Best regards, ' . config('app.name'));

    }
}