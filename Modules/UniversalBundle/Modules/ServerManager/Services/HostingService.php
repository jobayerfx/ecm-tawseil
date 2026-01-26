<?php

namespace Modules\ServerManager\Services;

use Modules\ServerManager\Entities\ServerHosting;
use Illuminate\Support\Collection;

class HostingService
{
    /**
     * Get all hostings for the current company
     */
    public function getAllHostings(): Collection
    {
        return ServerHosting::where('company_id', company()->id)
            ->with(['assignedTo', 'createdBy', 'updatedBy'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get active hostings
     */
    public function getActiveHostings(): Collection
    {
        return ServerHosting::where('company_id', company()->id)
            ->where('status', 'active')
            ->with(['assignedTo', 'createdBy', 'updatedBy'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get expiring hostings
     */
    public function getExpiringHostings(int $days = 30): Collection
    {
        return ServerHosting::where('company_id', company()->id)
            ->where('renewal_date', '<=', now()->addDays($days))
            ->where('status', 'active')
            ->with(['assignedTo', 'createdBy', 'updatedBy'])
            ->orderBy('renewal_date', 'asc')
            ->get();
    }

    /**
     * Get hosting statistics
     */
    public function getStatistics(): array
    {
        $companyId = company()->id;

        return [
            'total' => ServerHosting::where('company_id', $companyId)->count(),
            'active' => ServerHosting::where('company_id', $companyId)
                ->where('status', 'active')
                ->count(),
            'expiring' => ServerHosting::where('company_id', $companyId)
                ->where('renewal_date', '<=', now()->addDays(30))
                ->where('status', 'active')
                ->count(),
            'expired' => ServerHosting::where('company_id', $companyId)
                ->where('renewal_date', '<', now())
                ->where('status', 'active')
                ->count(),
        ];
    }

    /**
     * Create a new hosting
     */
    public function createHosting(array $data): ServerHosting
    {
        $data['company_id'] = company()->id;
        $data['created_by'] = user()->id;
        $data['updated_by'] = user()->id;

        return ServerHosting::create($data);
    }

    /**
     * Update hosting
     */
    public function updateHosting(ServerHosting $hosting, array $data): bool
    {
        $data['updated_by'] = user()->id;

        return $hosting->update($data);
    }

    /**
     * Delete hosting
     */
    public function deleteHosting(ServerHosting $hosting): bool
    {
        return $hosting->delete();
    }
}
