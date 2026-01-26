<?php

namespace Modules\ServerManager\Observers;

use Modules\ServerManager\Entities\ServerDomain;
use Modules\ServerManager\Entities\ServerLog;
use Modules\ServerManager\Events\DomainCreated;
use Modules\ServerManager\Events\DomainUpdated;

class ServerDomainObserver
{
    /**
     * Handle the ServerDomain "created" event.
     */
    public function created(ServerDomain $serverDomain): void
    {
        // Get the user who performed the action
        $performedBy = user() ? user()->id : ($serverDomain->created_by ?? null);
        $companyId = company() ? company()->id : $serverDomain->company_id;

        // Only create log if we have a valid user
        if ($performedBy) {
            // Log the creation
            ServerLog::create([
                'company_id' => $companyId,
                'entity_type' => ServerDomain::class,
                'entity_id' => $serverDomain->id,
                'action' => 'created',
                'performed_by' => $performedBy,
                'description' => 'Domain "' . $serverDomain->domain_name . '" was created',
            ]);
        }

        // Dispatch event
        event(new DomainCreated($serverDomain));
    }

    /**
     * Handle the ServerDomain "updated" event.
     */
    public function updated(ServerDomain $serverDomain): void
    {
        // Get the user who performed the action
        $performedBy = user() ? user()->id : ($serverDomain->updated_by ?? null);
        $companyId = company() ? company()->id : $serverDomain->company_id;

        // Only create log if we have a valid user
        if ($performedBy) {
            // Log the update
            ServerLog::create([
                'company_id' => $companyId,
                'entity_type' => ServerDomain::class,
                'entity_id' => $serverDomain->id,
                'action' => 'updated',
                'performed_by' => $performedBy,
                'description' => 'Domain "' . $serverDomain->domain_name . '" was updated',
            ]);
        }

        // Get changes
        $changes = $serverDomain->getChanges();
        unset($changes['updated_at']);

        // Dispatch event
        event(new DomainUpdated($serverDomain, $changes));
    }

    /**
     * Handle the ServerDomain "deleted" event.
     */
    public function deleted(ServerDomain $serverDomain): void
    {
        // Get the user who performed the action
        $performedBy = user() ? user()->id : null;
        $companyId = company() ? company()->id : $serverDomain->company_id;

        // Only create log if we have a valid user
        if ($performedBy) {
            // Log the deletion
            ServerLog::create([
                'company_id' => $companyId,
                'entity_type' => ServerDomain::class,
                'entity_id' => $serverDomain->id,
                'action' => 'deleted',
                'performed_by' => $performedBy,
                'description' => 'Domain "' . $serverDomain->domain_name . '" was deleted',
            ]);
        }
    }

    /**
     * Handle the ServerDomain "restored" event.
     */
    public function restored(ServerDomain $serverDomain): void
    {
        // Get the user who performed the action
        $performedBy = user() ? user()->id : null;
        $companyId = company() ? company()->id : $serverDomain->company_id;

        // Only create log if we have a valid user
        if ($performedBy) {
            // Log the restoration
            ServerLog::create([
                'company_id' => $companyId,
                'entity_type' => ServerDomain::class,
                'entity_id' => $serverDomain->id,
                'action' => 'restored',
                'performed_by' => $performedBy,
                'description' => 'Domain "' . $serverDomain->domain_name . '" was restored',
            ]);
        }
    }

    /**
     * Handle the ServerDomain "force deleted" event.
     */
    public function forceDeleted(ServerDomain $serverDomain): void
    {
        // Get the user who performed the action
        $performedBy = user() ? user()->id : null;
        $companyId = company() ? company()->id : $serverDomain->company_id;

        // Only create log if we have a valid user
        if ($performedBy) {
            // Log the force deletion
            ServerLog::create([
                'company_id' => $companyId,
                'entity_type' => ServerDomain::class,
                'entity_id' => $serverDomain->id,
                'action' => 'force_deleted',
                'performed_by' => $performedBy,
                'description' => 'Domain "' . $serverDomain->domain_name . '" was force deleted',
            ]);
        }
    }
}
