<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CustomResetPasswordNotification extends Notification
{
    private $token;
    private $expire;

    public function __construct($token, $expire)
    {
        $this->token = $token;
        $this->expire = $expire;
    }

    public function toMail($notifiable)
    {
        $url = url(route('password.reset', ['token' => $this->token, 'email' => $notifiable->email], false));

        return (new MailMessage())
            ->subject(__('Custom Password Reset Notification'))
            ->line(__('You are receiving this email because we received a password reset request for your account.'))
            ->action(__('Reset Password'), $url)
            ->line(__('This password reset link will expire in :count minutes.', ['count' => $this->expire]))
            ->line(__('If you did not request a password reset, no further action is required.'));
    }
}
