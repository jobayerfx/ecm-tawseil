<?php

namespace Modules\ServerManager\Traits;

trait HasServerManagerPermissions
{
    /**
     * Check if user has hosting permissions
     */
    public function hasHostingPermission(string $permission): bool
    {
        $userPermission = user()->permission($permission);
        return in_array($userPermission, ['all', 'added', 'owned', 'both']);
    }

    /**
     * Check if user has domain permissions
     */
    public function hasDomainPermission(string $permission): bool
    {
        $userPermission = user()->permission($permission);
        return in_array($userPermission, ['all', 'added', 'owned', 'both']);
    }

    /**
     * Check if user can view hosting
     */
    public function canViewHosting(): bool
    {
        return $this->hasHostingPermission('view_hosting');
    }

    /**
     * Check if user can add hosting
     */
    public function canAddHosting(): bool
    {
        return $this->hasHostingPermission('add_hosting');
    }

    /**
     * Check if user can edit hosting
     */
    public function canEditHosting(): bool
    {
        return $this->hasHostingPermission('edit_hosting');
    }

    /**
     * Check if user can delete hosting
     */
    public function canDeleteHosting(): bool
    {
        return $this->hasHostingPermission('delete_hosting');
    }

    /**
     * Check if user can view domains
     */
    public function canViewDomains(): bool
    {
        return $this->hasDomainPermission('view_domain');
    }

    /**
     * Check if user can add domains
     */
    public function canAddDomains(): bool
    {
        return $this->hasDomainPermission('add_domain');
    }

    /**
     * Check if user can edit domains
     */
    public function canEditDomains(): bool
    {
        return $this->hasDomainPermission('edit_domain');
    }

    /**
     * Check if user can delete domains
     */
    public function canDeleteDomains(): bool
    {
        return $this->hasDomainPermission('delete_domain');
    }
}
