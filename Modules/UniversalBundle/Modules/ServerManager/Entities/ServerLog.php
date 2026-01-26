<?php

namespace Modules\ServerManager\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Company;
use App\Models\User;

class ServerLog extends Model
{
    protected $fillable = [
        'company_id',
        'log_type',
        'action',
        'entity_type',
        'entity_id',
        'description',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'performed_by',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    /**
     * Get the company that owns the log.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the user who performed the action.
     */
    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    /**
     * Get the entity that was affected.
     */
    public function entity()
    {
        return $this->belongsTo($this->getEntityClass(), 'entity_id');
    }

    /**
     * Get the entity class based on entity type.
     */
    protected function getEntityClass(): string
    {
        return match($this->entity_type) {
            'hosting' => ServerHosting::class,
            'domain' => ServerDomain::class,
            default => ServerHosting::class, // Fallback to a concrete class instead of abstract Model
        };
    }

    /**
     * Get the action badge class.
     */
    public function getActionBadgeClass(): string
    {
        return match($this->action) {
            'created' => 'badge-success',
            'updated' => 'badge-info',
            'deleted' => 'badge-danger',
            'renewed' => 'badge-warning',
            'expired' => 'badge-secondary',
            default => 'badge-primary',
        };
    }
}
