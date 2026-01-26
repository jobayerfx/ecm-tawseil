<?php

namespace Modules\GroupMessage\Listeners;

use App\Models\User;
use App\Scopes\ActiveScope;
use Illuminate\Support\Facades\Notification;
use Modules\GroupMessage\Events\NewGroupMsgChatEvent;
use Modules\GroupMessage\Notifications\NewGroupMsgChat;

class NewGroupMsgChatListener
{

    /**
     * Handle the event.
     *
     * @param NewChatEvent $event
     * @return void
     */

    public function handle(NewGroupMsgChatEvent $event)
    {
        $notifyUser = User::withoutGlobalScope(ActiveScope::class)->findOrFail($event->userChat->user_id);
        Notification::send($notifyUser, new NewGroupMsgChat($event->userChat));
    }

}
