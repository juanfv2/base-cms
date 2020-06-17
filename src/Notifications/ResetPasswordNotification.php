<?php

namespace Juanfv2\BaseCms\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    use Queueable;
    /**
     * The password reset token.
     *
     * @var string
     */
    public $token;

    /**
     * Create a notification instance.
     *
     * @param  string $token
     * @return void
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        // $urlStr = config('fcm.FCM_SALDOPLUS_WELCOME_PAGE') . '/password/reset/' . $this->token . '/' . urlencode($notifiable->email);
        $urlStr = (config('base.welcome_page') == '' ? url('/') : config('base.welcome_page')) . '/base/password/?t=' . $this->token . '&e=' . urlencode($notifiable->email);

        logger(__FILE__ . ':' . __LINE__ . ' $urlStr ', [$urlStr]);

        return (new MailMessage)
            ->subject(__('auth.password.reset'))
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
     * @param  mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
