<?php

namespace QCod\Gamify\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use App\User;

class BadgeGivenEvent implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    /**
     * @var User
     */
    public $user;

    /**
     * @var Model
     */
    public $badge;

    /**
     * Create a new event instance.
     *
     * @param $user
     * @param $point integer
     * @param $increment
     */
    public function __construct(Model $badge, User $user)
    {
        $this->user = $user;
        $this->badge = $badge;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|\Illuminate\Broadcasting\Channel[]
     */
    public function broadcastOn()
    {
        $channelName = config('gamify.channel_name') . $this->user->getKey();

        if (config('gamify.broadcast_on_private_channel')) {
            return new PrivateChannel($channelName);
        }

        return new Channel($channelName);
    }
}
