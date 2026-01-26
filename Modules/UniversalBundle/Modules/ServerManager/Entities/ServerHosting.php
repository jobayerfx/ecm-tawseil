<?php

namespace Modules\ServerManager\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\ServerManager\Database\factories\ServerHostingFactory;
use App\Models\Company;
use App\Models\User;
use App\Models\Project;
use App\Models\ClientDetails;
use App\Scopes\ActiveScope;

class ServerHosting extends Model
{
    use HasFactory;
    protected $fillable = [
        'company_id',
        'name',
        'domain_name',
        'hosting_provider',
        'provider_url',
        'server_type',
        'ip_address',
        'username',
        'password',
        'control_panel',
        'control_panel_url',
        'cpanel_url',
        'project',
        'client',
        'ftp_host',
        'ftp_username',
        'ftp_password',
        'database_host',
        'database_name',
        'database_username',
        'database_password',
        'purchase_date',
        'renewal_date',
        'monthly_cost',
        'annual_cost',
        'billing_cycle',
        'status',
        'notes',
        'ssl_certificate_info',
        'ssl_certificate',
        'ssl_expiry_date',
        'ssl_type',
        // 'backup_info',
        // 'backup_frequency',
        // 'last_backup_date',
        'assigned_to',
        'created_by',
        'updated_by',
        'expiry_notification',
        'notification_days_before',
        'notification_time_unit',
        'last_notification_sent',
        'server_location',
        'disk_space',
        'bandwidth',
        'database_limit',
        'email_limit',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'renewal_date' => 'date',
        'ssl_expiry_date' => 'date',
        'last_backup_date' => 'date',
        'monthly_cost' => 'decimal:2',
        'annual_cost' => 'decimal:2',
        'expiry_notification' => 'boolean',
        'last_notification_sent' => 'datetime',
    ];

    protected $hidden = [
        'password',
        'ftp_password',
        'database_password',
    ];

    /**
     * Get the company that owns the hosting.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the user assigned to this hosting.
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the user who created this hosting.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this hosting.
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the domains associated with this hosting.
     */
    public function domains(): HasMany
    {
        return $this->hasMany(ServerDomain::class, 'hosting_id');
    }

    /**
     * Get the provider associated with this hosting.
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(ServerProvider::class, 'hosting_provider');
    }

    /**
     * Get the logs for this hosting.
     */
    public function logs(): HasMany
    {
        return $this->hasMany(ServerLog::class, 'entity_id')
            ->where('entity_type', 'hosting');
    }

    public function serverType(): BelongsTo
    {
        return $this->belongsTo(ServerType::class, 'server_type');
    }

    /**
     * Get the project associated with this hosting.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project');
    }

    public function projectDetails(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project');
    }

    /**
     * Get the client associated with this hosting.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(ClientDetails::class, 'client');
    }

    public function clientDetails(): BelongsTo
    {
        return $this->belongsTo(ClientDetails::class, 'client');
    }

    public function clientDetail(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client')->withoutGlobalScope(ActiveScope::class);
    }

    /**
     * Check if hosting is expiring soon.
     */
    public function isExpiringSoon(int $days = 30): bool
    {
        return $this->renewal_date && $this->renewal_date->diffInDays(now()) <= $days;
    }

    /**
     * Get the number of days until expiry.
     */
    public function daysUntilExpiry(): int
    {
        if (!$this->renewal_date) {
            return 0;
        }

        $now = now()->startOfDay();
        $expiry = $this->renewal_date->copy()->startOfDay();

        $diff = $expiry->diffInDays($now, false);

        // Always return the absolute number of days (no negative)
        return abs($diff);
    }

    /**
     * Check if notification should be sent for this hosting.
     */
    public function shouldSendNotification(): bool
    {
        if (!$this->expiry_notification || !$this->notification_days_before || !$this->renewal_date) {
            return false;
        }

        $notificationDate = $this->getNotificationDate();
        $today = now()->startOfDay();

        // Check if today is the notification date and notification hasn't been sent today
        return $notificationDate->equalTo($today) &&
               (!$this->last_notification_sent || !$this->last_notification_sent->isToday());
    }

    /**
     * Get the date when notification should be sent.
     */
    public function getNotificationDate(): \Carbon\Carbon
    {
        $daysBefore = $this->notification_days_before;

        switch ($this->notification_time_unit) {
            case 'weeks':
                $daysBefore = $daysBefore * 7;
                break;
            case 'months':
                $daysBefore = $daysBefore * 30;
                break;
        }

        return $this->renewal_date->subDays($daysBefore)->startOfDay();
    }

    /**
     * Mark notification as sent.
     */
    public function markNotificationSent(): void
    {
        $this->update(['last_notification_sent' => now()]);
    }

    /**
     * Check if SSL is expiring soon.
     */
    public function isSslExpiringSoon(int $days = 60): bool
    {
        return $this->ssl_expiry_date && $this->ssl_expiry_date->diffInDays(now()) <= $days;
    }

    /**
     * Get the status badge class.
     */
    public function getStatusBadgeClass(): string
    {
        switch($this->status) {
            case 'active':
                return 'badge-success';
            case 'suspended':
                return 'badge-warning';
            case 'expired':
                return 'badge-danger';
            case 'cancelled':
                return 'badge-secondary';
            default:
                return 'badge-info';
        }
    }

    /**
     * Encrypt sensitive data before saving.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($hosting) {
            // Only encrypt if the field is dirty (changed) and not empty
            if ($hosting->isDirty('password') && !empty($hosting->password)) {
                $hosting->password = encrypt($hosting->password);
            }
            if ($hosting->isDirty('ftp_password') && !empty($hosting->ftp_password)) {
                $hosting->ftp_password = encrypt($hosting->ftp_password);
            }
            if ($hosting->isDirty('database_password') && !empty($hosting->database_password)) {
                $hosting->database_password = encrypt($hosting->database_password);
            }
        });
    }

    /**
     * Decrypt sensitive data when accessing.
     */
    public function getPasswordAttribute($value)
    {
        if (empty($value)) {
            return null;
        }

        try {
            return decrypt($value);
        } catch (\Exception $e) {
            // If decryption fails, return the original value (might be already decrypted)
            return $value;
        }
    }

    public function getFtpPasswordAttribute($value)
    {
        if (empty($value)) {
            return null;
        }

        try {
            return decrypt($value);
        } catch (\Exception $e) {
            // If decryption fails, return the original value (might be already decrypted)
            return $value;
        }
    }

    public function getDatabasePasswordAttribute($value)
    {
        if (empty($value)) {
            return null;
        }

        try {
            return decrypt($value);
        } catch (\Exception $e) {
            // If decryption fails, return the original value (might be already decrypted)
            return $value;
        }
    }

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return ServerHostingFactory::new();
    }
}
