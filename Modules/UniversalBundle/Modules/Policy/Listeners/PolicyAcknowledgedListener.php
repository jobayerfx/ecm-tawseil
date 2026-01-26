<?php

namespace Modules\Policy\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;
use Modules\Policy\Events\PolicyAcknowledgedEvent;
use Modules\Policy\Notifications\PolicyAcknowledgedNotification;

class PolicyAcknowledgedListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(PolicyAcknowledgedEvent $event): void
    {
        Notification::send($event->notifyUsers, new PolicyAcknowledgedNotification($event->policy, $event->acknowledgeBy));
    }

}
