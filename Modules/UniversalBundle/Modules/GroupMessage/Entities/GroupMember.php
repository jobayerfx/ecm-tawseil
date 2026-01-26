<?php

namespace Modules\GroupMessage\Entities;

use App\Models\BaseModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GroupMember extends BaseModel
{

    use HasFactory;

    protected $table = 'group_members';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'group_id',
        'user_id'
    ];

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
