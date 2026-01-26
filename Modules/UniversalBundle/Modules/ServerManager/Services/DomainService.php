<?php

namespace Modules\ServerManager\Services;

use Modules\ServerManager\Entities\ServerDomain;
use Illuminate\Support\Collection;

class DomainService
{
    /**
     * Get all domains for the current company
     */
    public function getAllDomains(): Collection
    {
        return ServerDomain::where('company_id', company()->id)
            ->with(['assignedTo', 'createdBy', 'updatedBy', 'hosting'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get active domains
     */
    public function getActiveDomains(): Collection
    {
        return ServerDomain::where('company_id', company()->id)
            ->where('status', 'active')
            ->with(['assignedTo', 'createdBy', 'updatedBy', 'hosting'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get expiring domains
     */
    public function getExpiringDomains(int $days = 30): Collection
    {
        return ServerDomain::where('company_id', company()->id)
            ->where('expiry_date', '<=', now()->addDays($days))
            ->where('status', 'active')
            ->with(['assignedTo', 'createdBy', 'updatedBy', 'hosting'])
            ->orderBy('expiry_date', 'asc')
            ->get();
    }

    /**
     * Get domain statistics
     */
    public function getStatistics(): array
    {
        $companyId = company()->id;

        return [
            'total' => ServerDomain::where('company_id', $companyId)->count(),
            'active' => ServerDomain::where('company_id', $companyId)
                ->where('status', 'active')
                ->count(),
            'expiring' => ServerDomain::where('company_id', $companyId)
                ->where('expiry_date', '<=', now()->addDays(30))
                ->where('status', 'active')
                ->count(),
            'expired' => ServerDomain::where('company_id', $companyId)
                ->where('expiry_date', '<', now())
                ->where('status', 'active')
                ->count(),
        ];
    }

    /**
     * Create a new domain
     */
    public function createDomain(array $data): ServerDomain
    {
        $data['company_id'] = company()->id;
        $data['created_by'] = user()->id;
        $data['updated_by'] = user()->id;

        return ServerDomain::create($data);
    }

    /**
     * Update domain
     */
    public function updateDomain(ServerDomain $domain, array $data): bool
    {
        $data['updated_by'] = user()->id;

        return $domain->update($data);
    }

    /**
     * Delete domain
     */
    public function deleteDomain(ServerDomain $domain): bool
    {
        return $domain->delete();
    }
}
