<?php

namespace Modules\GroupMessage\Listeners;

use Illuminate\Support\Facades\Notification;
use Modules\GroupMessage\Events\NewGroupMentionChatEvent;
use Modules\GroupMessage\Notifications\NewGroupMentionChat;

class NewGroupMentionChatListener
{

    /**
     * Handle the event.
     *
     * @param NewGroupMentionChatEvent $event
     * @return void
     */

    public function handle(NewGroupMentionChatEvent $event)
    {
        Notification::send($event->notifyUser, new NewGroupMentionChat($event->userChat));
    }

}
