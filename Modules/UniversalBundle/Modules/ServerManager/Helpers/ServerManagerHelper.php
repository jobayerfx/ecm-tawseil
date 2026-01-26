<?php

namespace Modules\ServerManager\Helpers;

class ServerManagerHelper
{
    /**
     * Get server type options
     */
    public static function getServerTypeOptions(): array
    {
        return [
            'shared' => 'Shared Hosting',
            'vps' => 'VPS',
            'dedicated' => 'Dedicated Server',
            'cloud' => 'Cloud Hosting',
        ];
    }

    /**
     * Get billing cycle options
     */
    public static function getBillingCycleOptions(): array
    {
        return [
            'monthly' => 'Monthly',
            'quarterly' => 'Quarterly',
            'semi_annually' => 'Semi-Annually',
            'annually' => 'Annually',
            'biennially' => 'Biennially',
        ];
    }

    /**
     * Get status options
     */
    public static function getStatusOptions(): array
    {
        return [
            'active' => 'Active',
            'inactive' => 'Inactive',
            'expired' => 'Expired',
            'suspended' => 'Suspended',
            'cancelled' => 'Cancelled',
        ];
    }

    /**
     * Get domain type options
     */
    public static function getDomainTypeOptions(): array
    {
        return [
            'com' => '.com',
            'net' => '.net',
            'org' => '.org',
            'info' => '.info',
            'biz' => '.biz',
            'co' => '.co',
            'io' => '.io',
            'me' => '.me',
            'tv' => '.tv',
            'app' => '.app',
            'dev' => '.dev',
            'tech' => '.tech',
            'online' => '.online',
            'site' => '.site',
            'store' => '.store',
            'blog' => '.blog',
            'cc' => '.cc',
            'ws' => '.ws',
            'other' => 'Other',
        ];
    }

    /**
     * Format file size
     */
    public static function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get days until expiry
     */
    public static function getDaysUntilExpiry(string $expiryDate): int
    {
        $expiry = \Carbon\Carbon::parse($expiryDate);
        return now()->diffInDays($expiry, false);
    }

    /**
     * Check if item is expiring soon
     */
    public static function isExpiringSoon(string $expiryDate, int $days = 30): bool
    {
        $daysUntilExpiry = self::getDaysUntilExpiry($expiryDate);
        return $daysUntilExpiry <= $days && $daysUntilExpiry > 0;
    }

    /**
     * Check if item is expired
     */
    public static function isExpired(string $expiryDate): bool
    {
        return self::getDaysUntilExpiry($expiryDate) < 0;
    }
}
