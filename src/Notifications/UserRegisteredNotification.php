<?php

namespace Juanfv2\BaseCms\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserRegisteredNotification extends Notification
{
    use Queueable;
    /**
     * undocumented class variable
     *
     * @var \App\Models\Auth\User
     **/
    public $user;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($user)
    {
        $this->user = $user;
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
        $urlStr = (config('base.welcome_page') == '' ? url('/') : config('base.welcome_page')) . '/base/welcome/?t=' . $this->user->verifyUser->token;

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
