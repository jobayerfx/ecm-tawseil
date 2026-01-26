<?php

namespace Modules\Policy\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;
use Modules\Policy\Events\SendReminderEvent;
use Modules\Policy\Notifications\SendReminderNotification;

class SendReminderListener implements ShouldQueue
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
    public function handle(SendReminderEvent $event): void
    {
        Notification::send($event->notifyUsers, new SendReminderNotification($event->policy));
    }

}
