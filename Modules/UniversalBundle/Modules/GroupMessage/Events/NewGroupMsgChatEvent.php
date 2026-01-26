<?php

namespace Modules\GroupMessage\Events;

use Illuminate\Queue\SerializesModels;
use Modules\GroupMessage\Entities\UserChat;
use Illuminate\Foundation\Events\Dispatchable;

class NewGroupMsgChatEvent
{

    use Dispatchable, SerializesModels;

    public $userChat;

    public function __construct(UserChat $userChat)
    {
        $this->userChat = $userChat;
    }

    public function broadcastOn()
    {
        return ['messages-channel'];
    }

    public function broadcastAs()
    {
        return 'messages.received';
    }

}
