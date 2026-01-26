<?php

namespace Modules\ServerManager\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Company;
use App\Models\User;
use App\Traits\HasCompany;

class ServerProvider extends Model
{
    use HasCompany;

    protected $fillable = [
        'company_id',
        'name',
        'url',
        'type',
        'description',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the company that owns the provider.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the user who created this provider.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who updated this provider.
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the domains associated with this provider.
     */
    public function domains(): HasMany
    {
        return $this->hasMany(ServerDomain::class, 'domain_provider', 'name');
    }

    /**
     * Get the hostings associated with this provider.
     */
    public function hostings(): HasMany
    {
        return $this->hasMany(ServerHosting::class, 'hosting_provider', 'name');
    }

    /**
     * Get the logs for this provider.
     */
    public function logs(): HasMany
    {
        return $this->hasMany(ServerLog::class, 'provider_id');
    }

    /**
     * Check if the provider is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Get the status badge class.
     */
    public function getStatusBadgeClass(): string
    {
        return match ($this->status) {
            'active' => 'bg-success',
            'inactive' => 'bg-danger',
            default => 'bg-secondary',
        };
    }

    /**
     * Get the type badge class.
     */
    public function getTypeBadgeClass(): string
    {
        return match ($this->type) {
            'domain' => 'bg-info',
            'hosting' => 'bg-warning',
            'both' => 'bg-primary',
            default => 'bg-secondary',
        };
    }

    /**
     * Scope to get only active providers.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to get providers by type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Boot method to handle model events.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($provider) {
            if (auth()->check()) {
                $provider->created_by = auth()->id();
            }

            // Ensure company_id is set if not already set
            if (!$provider->company_id && company()) {
                $provider->company_id = company()->id;
            }
        });

        static::updating(function ($provider) {
            if (auth()->check()) {
                $provider->updated_by = auth()->id();
            }
        });
    }
}
