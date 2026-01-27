<?php

namespace Modules\Asset\Entities;

use App\Models\BaseModel;
use App\Models\User;
use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetMaintenance extends BaseModel
{
    use HasCompany;

    protected $table = 'asset_maintenance';

    CONST STATUSES = [
        'scheduled' => 'text-blue',
        'inprogress' => 'text-yellow',
        'completed' => 'text-light-green',
        'overdue' => 'text-red',
        'cancelled' => 'text-dark-grey',
    ];

    CONST TYPES = [
        'planned' => 'Planned Maintenance',
        'reactive' => 'Reactive Maintenance',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'company_id',
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
        'company_id',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'scheduled_date',
        'due_date',
        'started_at',
        'completed_at',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Check if maintenance is overdue
     */
    public function isOverdue(): bool
    {
        if ($this->status === 'completed') {
            return false;
        }

        $checkDate = $this->due_date ?? $this->scheduled_date;
        
        return $checkDate && $checkDate->isPast();
    }

    /**
     * Update status based on dates
     */
    public function updateStatus(): void
    {
        // Don't auto-update if completed or cancelled
        if ($this->status === 'completed' || $this->status === 'cancelled') {
            return;
        }

        if ($this->isOverdue()) {
            $this->status = 'overdue';
        } elseif ($this->started_at && !$this->completed_at) {
            $this->status = 'inprogress';
        } elseif ($this->scheduled_date->isFuture() || ($this->due_date && $this->due_date->isFuture())) {
            $this->status = 'scheduled';
        }

        $this->save();
    }
}

