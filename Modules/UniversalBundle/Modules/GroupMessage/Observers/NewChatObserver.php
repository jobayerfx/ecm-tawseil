<?php

namespace Modules\GroupMessage\Observers;

use App\Models\User;
use App\Models\Notification;
use Illuminate\Support\Facades\Config;
use Modules\GroupMessage\Entities\UserChat;
use Modules\GroupMessage\Events\NewGroupMentionChatEvent;
use Modules\GroupMessage\Events\NewGroupMsgChatEvent;
use Modules\GroupMessage\Events\NewMessage;

class NewChatObserver
{

    public function created(UserChat $userChat)
    {
        if (!isRunningInConsoleOrSeeding() && (is_null($userChat->group_id) && is_null($userChat->channel_id))) {

            if ((request()->user_id == request()->mention_user_id) && request()->mention_user_id != null && request()->mention_user_id != '') {
                $userChat->mentionUser()->sync(request()->mention_user_id);
                $mentionUserIds = explode(',', request()->mention_user_id);
                $mentionUser = User::whereIn('id', $mentionUserIds)->get();

                event(new NewGroupMentionChatEvent($userChat, $mentionUser));
            }
            else {
                event(new NewGroupMsgChatEvent($userChat));
            }

            if (pusher_settings()->status == 1 && pusher_settings()->messages == 1) {
                Config::set('queue.default', 'sync'); // Set intentionally for instant delivery of messages
                broadcast(new NewMessage($userChat))->toOthers()->via('pusher');
            }
        }
    }

    public function creating(UserChat $userChat)
    {
        if (company()) {
            $userChat->company_id = company()->id;
        }
    }

    public function deleting(UserChat $userChat)
    {
        $notifyData = ['Modules\GroupMessage\Notifications\NewChat'];
        Notification::deleteNotification($notifyData, $userChat->id);

        $notifyData = ['Modules\GroupMessage\Notifications\NewGroupMsgChat'];
        Notification::deleteNotification($notifyData, $userChat->id);
    }

}
