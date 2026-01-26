<?php

namespace Modules\GroupMessage\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\GroupMessage\Entities\UserChat;

class NewMessage
{

    use Dispatchable, SerializesModels;

    public $userChat;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(UserChat $userChat)
    {
        $this->userChat = $userChat;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('chat');
    }

}
