<?php

namespace Modules\ServerManager\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Modules\ServerManager\Events\HostingCreated;

class HostingCreatedListener implements ShouldQueue
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
    public function handle(HostingCreated $event): void
    {
        $hosting = $event->hosting;

        // Log additional information if needed
        Log::info('Hosting created: ' . $hosting->name, [
            'hosting_id' => $hosting->id,
            'created_by' => $hosting->created_by,
            'assigned_to' => $hosting->assigned_to,
        ]);
    }
}
