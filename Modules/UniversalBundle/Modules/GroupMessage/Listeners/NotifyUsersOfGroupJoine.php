<?php

namespace Modules\GroupMessage\Listeners;

use Illuminate\Support\Facades\Notification;
use Modules\GroupMessage\Events\NewGroupJoin;
use Modules\GroupMessage\Notifications\NotifyGroupJoinee;

class NotifyUsersOfGroupJoine
{

    /**
     * Handle the event.
     */
    public function handle(NewGroupJoin $event): void
    {
        Notification::send($event->memberIds, new NotifyGroupJoinee($event->group));
    }

}
