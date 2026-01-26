<?php

namespace Modules\GroupMessage\Events;

use Illuminate\Queue\SerializesModels;
use Modules\GroupMessage\Entities\UserChat;
use Illuminate\Foundation\Events\Dispatchable;

class NewGroupMentionChatEvent
{

    use Dispatchable, SerializesModels;

    public $userChat;
    public $notifyUser;

    public function __construct(UserChat $userChat, $notifyUser)
    {
        $this->userChat = $userChat;
        $this->notifyUser = $notifyUser;

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
