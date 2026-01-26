<?php

namespace Modules\ServerManager\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\ServerManager\Database\factories\ServerDomainFactory;
use App\Models\Company;
use App\Models\User;
use App\Models\Project;
use App\Models\ClientDetails;

class ServerDomain extends Model
{
    use HasFactory;
    protected $fillable = [
        'company_id',
        'domain_name',
        'domain_provider',
        'provider_url',
        'domain_type',
        'registrar',
        'registrar_url',
        'registrar_username',
        'registrar_password',
        'registrar_status',
        'registration_date',
        'expiry_date',
        'renewal_date',
        'username',
        'password',
        'annual_cost',
        'billing_cycle',
        'status',
        'dns_provider',
        'dns_status',
        'nameservers',
        'dns_records',
        'whois_protection',
        'auto_renewal',
        'notes',
        'hosting_id',
        'project_id',
        'client_id',
        'assigned_to',
        'created_by',
        'updated_by',
        'expiry_notification',
        'notification_days_before',
        'notification_time_unit',
        'last_notification_sent',
    ];

    protected $casts = [
        'registration_date' => 'date',
        'expiry_date' => 'date',
        'renewal_date' => 'date',
        'annual_cost' => 'decimal:2',
        'nameservers' => 'array',
        'dns_records' => 'array',
        'expiry_notification' => 'boolean',
        'last_notification_sent' => 'datetime',
    ];

    protected $hidden = [
        'registrar_password',
        'password',
    ];

    /**
     * Get the company that owns the domain.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the hosting associated with this domain.
     */
    public function hosting(): BelongsTo
    {
        return $this->belongsTo(ServerHosting::class, 'hosting_id');
    }

    /**
     * Get the provider associated with this domain.
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(ServerProvider::class, 'domain_provider');
    }

    /**
     * Get the user assigned to this domain.
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the user who created this domain.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this domain.
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the logs for this domain.
     */
    public function logs(): HasMany
    {
        return $this->hasMany(ServerLog::class, 'entity_id')
            ->where('entity_type', 'domain');
    }

    /**
     * Get the project associated with this domain.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    /**
     * Get the client associated with this domain.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(ClientDetails::class, 'client_id');
    }

    public function clientDetail(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id')->withoutGlobalScope(ActiveScope::class);
    }

    /**
     * Check if domain is expiring soon.
     */
    public function isExpiringSoon(int $days = 30): bool
    {
        return $this->expiry_date && $this->expiry_date->diffInDays(now()) <= $days;
    }

    /**
     * Get the number of days until expiry.
     */
    public function daysUntilExpiry(): int
    {
        if (!$this->expiry_date) {
            return 0;
        }

        $now = now()->startOfDay();
        $expiry = $this->expiry_date->copy()->startOfDay();

        $diff = $expiry->diffInDays($now, false);

        // Always return the absolute number of days (no negative)
        return abs($diff);
    }

    /**
     * Check if notification should be sent for this domain.
     */
    public function shouldSendNotification(): bool
    {
        if (!$this->expiry_notification || !$this->notification_days_before || !$this->expiry_date) {
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

        return $this->expiry_date->subDays($daysBefore)->startOfDay();
    }

    /**
     * Mark notification as sent.
     */
    public function markNotificationSent(): void
    {
        $this->update(['last_notification_sent' => now()]);
    }

    /**
     * Check if domain is expired.
     */
    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    /**
     * Get the status badge class.
     */
    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            'active' => 'badge-success',
            'expired' => 'badge-danger',
            'suspended' => 'badge-warning',
            'transferred' => 'badge-info',
            default => 'badge-secondary',
        };
    }

    /**
     * Encrypt sensitive data before saving.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($domain) {
            if ($domain->isDirty('registrar_password') && !empty($domain->registrar_password)) {
                $domain->registrar_password = encrypt($domain->registrar_password);
            }
            if ($domain->isDirty('password') && !empty($domain->password)) {
                $domain->password = encrypt($domain->password);
            }
        });
    }

    /**
     * Decrypt sensitive data when accessing.
     */
    public function getRegistrarPasswordAttribute($value)
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

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return ServerDomainFactory::new();
    }
}
