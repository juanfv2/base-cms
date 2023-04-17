<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    /**
     * Create a notification instance.
     *
     * @param  string  $token
     * @return void
     */
    public function __construct(public $token)
    {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array
     */
    public function via(mixed $notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail(mixed $notifiable)
    {
        $urlStr = (config('base.welcome_page') == '' ? url('/') : config('base.welcome_page')).'/base/password/?t='.$this->token.'&e='.urlencode((string) $notifiable->email);

        return (new MailMessage)
            ->subject(__('auth.password.reset.subject'))
            ->line(__('auth.password.reset.line.1'))
            ->action(__('auth.password.reset.action'), $urlStr)
            ->line(__('auth.password.reset.line.2'))
            ->markdown('vendor.notifications.email', [
                'greeting' => __('messages.mail.change'),
                'salutation' => __('messages.mail.salutation'),
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array
     */
    public function toArray(mixed $notifiable)
    {
        return [
            //
        ];
    }
}
