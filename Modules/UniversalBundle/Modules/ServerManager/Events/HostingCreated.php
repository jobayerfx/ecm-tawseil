<?php

namespace Modules\ServerManager\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\ServerManager\Entities\ServerHosting;

class HostingCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $hosting;

    /**
     * Create a new event instance.
     */
    public function __construct(ServerHosting $hosting)
    {
        $this->hosting = $hosting;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}
