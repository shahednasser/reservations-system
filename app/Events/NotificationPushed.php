<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class NotificationPushed implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    private $user;
    private $text;
    private $url;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($user, $text, $url)
    {
        $this->user = $user;
        $this->text = $text;
        $this->url = $url;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('App.User.'.$this->user->id);
    }

    public function broadcastAs(){
      return 'UserNotifications';
    }

    public function broadcastWith(){
      return ['id' => $this->user->id, 'text' => $this->text, 'url' => $this->url];
    }
}
