<?php

namespace Modules\ServerManager\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\ServerManager\Entities\ServerDomain;

class DomainUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $domain;
    public $changes;

    /**
     * Create a new event instance.
     */
    public function __construct(ServerDomain $domain, array $changes = [])
    {
        $this->domain = $domain;
        $this->changes = $changes;
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
