<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserRegisteredNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     * @param \App\Models\Auth\User $user
     */
    public function __construct(
        /**
         * undocumented class variable
         **/
        public $user
    )
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
        $urlStr = (config('base.welcome_page') == '' ? url('/') : config('base.welcome_page')).'/base/welcome/?t='.$this->user->verifyUser->token;

        // logger(__FILE__ . ':' . __LINE__ . ' $urlStr ', [$urlStr]);

        return (new MailMessage)
            ->subject(__('messages.mail.welcome', ['app_name' => config('app.name')]))
            ->action(__('messages.mail.verifyTitle'), $urlStr)
            ->markdown('vendor.notifications.email', [
                'user' => $this->user,
                'greeting' => __('messages.mail.welcome', ['app_name' => config('app.name')]),
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
