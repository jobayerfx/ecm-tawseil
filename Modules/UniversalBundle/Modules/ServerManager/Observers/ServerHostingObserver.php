<?php

namespace Modules\ServerManager\Observers;

use Modules\ServerManager\Entities\ServerHosting;
use Modules\ServerManager\Entities\ServerLog;
use Modules\ServerManager\Events\HostingCreated;
use Modules\ServerManager\Events\HostingUpdated;

class ServerHostingObserver
{
    /**
     * Handle the ServerHosting "created" event.
     */
    public function created(ServerHosting $serverHosting): void
    {
        // Get the user who performed the action
        $performedBy = user() ? user()->id : ($serverHosting->created_by ?? null);

        // Only create log if we have a valid user
        if ($performedBy) {
            // Log the creation
            ServerLog::create([
                'company_id' => company() ? company()->id : $serverHosting->company_id,
                'entity_type' => ServerHosting::class,
                'entity_id' => $serverHosting->id,
                'action' => 'created',
                'performed_by' => $performedBy,
                'description' => 'Hosting "' . $serverHosting->name . '" was created',
            ]);
        }

        // Dispatch event
        event(new HostingCreated($serverHosting));
    }

    /**
     * Handle the ServerHosting "updated" event.
     */
    public function updated(ServerHosting $serverHosting): void
    {
        // Get the user who performed the action
        $performedBy = user() ? user()->id : ($serverHosting->updated_by ?? null);

        // Only create log if we have a valid user
        if ($performedBy) {
            // Log the update
            ServerLog::create([
                'company_id' => company() ? company()->id : $serverHosting->company_id,
                'entity_type' => ServerHosting::class,
                'entity_id' => $serverHosting->id,
                'action' => 'updated',
                'performed_by' => $performedBy,
                'description' => 'Hosting "' . $serverHosting->name . '" was updated',
            ]);
        }

        // Get changes
        $changes = $serverHosting->getChanges();
        unset($changes['updated_at']);

        // Dispatch event
        event(new HostingUpdated($serverHosting, $changes));
    }

    /**
     * Handle the ServerHosting "deleted" event.
     */
    public function deleted(ServerHosting $serverHosting): void
    {
        // Get the user who performed the action
        $performedBy = user() ? user()->id : null;

        // Only create log if we have a valid user
        if ($performedBy) {
            // Log the deletion
            ServerLog::create([
                'company_id' => company() ? company()->id : $serverHosting->company_id,
                'entity_type' => ServerHosting::class,
                'entity_id' => $serverHosting->id,
                'action' => 'deleted',
                'performed_by' => $performedBy,
                'description' => 'Hosting "' . $serverHosting->name . '" was deleted',
            ]);
        }
    }

    /**
     * Handle the ServerHosting "restored" event.
     */
    public function restored(ServerHosting $serverHosting): void
    {
        // Get the user who performed the action
        $performedBy = user() ? user()->id : null;

        // Only create log if we have a valid user
        if ($performedBy) {
            // Log the restoration
            ServerLog::create([
                'company_id' => company() ? company()->id : $serverHosting->company_id,
                'entity_type' => ServerHosting::class,
                'entity_id' => $serverHosting->id,
                'action' => 'restored',
                'performed_by' => $performedBy,
                'description' => 'Hosting "' . $serverHosting->name . '" was restored',
            ]);
        }
    }

    /**
     * Handle the ServerHosting "force deleted" event.
     */
    public function forceDeleted(ServerHosting $serverHosting): void
    {
        // Get the user who performed the action
        $performedBy = user() ? user()->id : null;

        // Only create log if we have a valid user
        if ($performedBy) {
            // Log the force deletion
            ServerLog::create([
                'company_id' => company() ? company()->id : $serverHosting->company_id,
                'entity_type' => ServerHosting::class,
                'entity_id' => $serverHosting->id,
                'action' => 'force_deleted',
                'performed_by' => $performedBy,
                'description' => 'Hosting "' . $serverHosting->name . '" was force deleted',
            ]);
        }
    }
}
