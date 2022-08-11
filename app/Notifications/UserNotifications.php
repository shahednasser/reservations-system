<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Broadcasting\PrivateChannel;

class UserNotifications extends Notification
{
    use Queueable;

    private $text;
    private $url;
    private $date_created;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($text, $url)
    {
        $this->text = $text;
        $this->url = $url;
        $this->date_created = \Carbon\Carbon::now()->toDateTimeString();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'text' => $this->text,
            'url' => url($this->url),
            'date' => $this->date_created
        ];
    }

    public function toBroadcast($notifiable){
      return new BroadcastMessage($this->toArray($notifiable));
    }
}
