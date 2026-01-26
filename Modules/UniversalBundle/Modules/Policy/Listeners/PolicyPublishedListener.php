<?php

namespace Modules\Policy\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;
use Modules\Policy\Events\PolicyPublishedEvent;
use Modules\Policy\Notifications\PolicyPublishedNotification;

class PolicyPublishedListener
{

    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(PolicyPublishedEvent $event): void
    {
        Notification::send($event->notifyUsers, new PolicyPublishedNotification($event->policy));
    }

}
