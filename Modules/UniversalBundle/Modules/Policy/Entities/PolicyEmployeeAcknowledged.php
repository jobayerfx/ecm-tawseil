<?php

namespace Modules\Policy\Entities;

use App\Models\User;
use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Modules\Policy\Database\factories\PolicyEmployeeAcknowledgedFactory;
use App\Scopes\ActiveScope;

class PolicyEmployeeAcknowledged extends BaseModel
{

    protected $dates = ['acknowledged_on'];

    protected $table = 'policy_employee_acknowledged';

    protected $appends = ['employee_signature'];

    public function users(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id')->withoutGlobalScope(ActiveScope::class);
    }

    public function policy (): BelongsTo
    {
        return $this->belongsTo(Policy::class, 'policy_id', 'id');
    }

    public function getEmployeeSignatureAttribute()
    {
        return asset_url_local_s3('policy/sign/' . $this->signature_file);
    }

}
