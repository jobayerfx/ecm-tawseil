<?php

namespace Modules\Aitools\Entities;

use App\Models\User;
use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Model;

class AiToolsUsageHistory extends Model
{
    use HasCompany;

    protected $table = 'ai_tools_usage_history';

    protected $guarded = ['id'];

    protected $casts = [
        'prompt_tokens' => 'integer',
        'completion_tokens' => 'integer',
        'total_tokens' => 'integer',
        'total_requests' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

