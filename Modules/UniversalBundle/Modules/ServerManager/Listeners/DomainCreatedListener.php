<?php

namespace Modules\ServerManager\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Modules\ServerManager\Events\DomainCreated;

class DomainCreatedListener implements ShouldQueue
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
    public function handle(DomainCreated $event): void
    {
        $domain = $event->domain;

        // Log additional information if needed
        Log::info('Domain created: ' . $domain->domain_name, [
            'domain_id' => $domain->id,
            'created_by' => $domain->created_by,
            'assigned_to' => $domain->assigned_to,
        ]);
    }
}
