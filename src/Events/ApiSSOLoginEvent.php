<?php

namespace Lysice\LaravelSSO\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ApiSSOLoginEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;

    public $flag;

    /**
     * WeChatSSOLoginEvent constructor.
     * @param $user
     * @param $flag
     */
    public function __construct($user, $flag)
    {
        $this->user = $user;
        $this->flag = $flag;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
