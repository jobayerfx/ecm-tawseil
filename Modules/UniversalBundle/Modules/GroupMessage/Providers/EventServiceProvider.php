<?php

namespace Modules\GroupMessage\Providers;

use App\Events\NewCompanyCreatedEvent;
use Modules\GroupMessage\Entities\UserChat;
use Modules\GroupMessage\Events\NewGroupJoin;
use Modules\GroupMessage\Observers\NewChatObserver;
use Modules\GroupMessage\Listeners\CompanyCreatedListener;
use Modules\GroupMessage\Listeners\NotifyUsersOfGroupJoine;
use Modules\GroupMessage\Events\NewGroupMentionChatEvent;
use Modules\GroupMessage\Events\NewGroupMsgChatEvent;
use Modules\GroupMessage\Listeners\NewGroupMentionChatListener;
use Modules\GroupMessage\Listeners\NewGroupMsgChatListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{

    protected $listen = [
        NewCompanyCreatedEvent::class => [CompanyCreatedListener::class],
        NewGroupJoin::class => [NotifyUsersOfGroupJoine::class],
        NewGroupMsgChatEvent::class => [NewGroupMsgChatListener::class],
        NewGroupMentionChatEvent::class => [NewGroupMentionChatListener::class],
    ];

    protected $observers = [
        UserChat::class => [NewChatObserver::class],
    ];

}
