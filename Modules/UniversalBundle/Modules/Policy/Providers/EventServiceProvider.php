<?php

namespace Modules\Policy\Providers;

use App\Events\NewCompanyCreatedEvent;
use Modules\Policy\Events\SendReminderEvent;
use Modules\Policy\Events\PolicyAcknowledgedEvent;
use Modules\Policy\Listeners\SendReminderListener;
use Modules\Policy\Listeners\CompanyCreatedListener;
use Modules\Policy\Listeners\PolicyAcknowledgedListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Policy\Events\PolicyPublishedEvent;
use Modules\Policy\Listeners\PolicyPublishedListener;

class EventServiceProvider extends ServiceProvider
{

    protected $listen = [
        NewCompanyCreatedEvent::class => [CompanyCreatedListener::class],
        SendReminderEvent::class => [SendReminderListener::class],
        PolicyAcknowledgedEvent::class => [PolicyAcknowledgedListener::class],
        PolicyPublishedEvent::class => [PolicyPublishedListener::class]
    ];

}
