<?php

namespace JawabApp\Gamify\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ReputationChanged implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    /**
     * @var Model
     */
    public $user;

    /**
     * @var int
     */
    public $point;

    /**
     * @var bool
     */
    public $increment;

    /**
     * Create a new event instance.
     *
     * @param $user
     * @param $point integer
     * @param $increment
     */
    public function __construct(Model $user, int $point, bool $increment)
    {
        $this->user = $user;
        $this->point = $point;
        $this->increment = $increment;
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
