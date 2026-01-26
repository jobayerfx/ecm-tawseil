<?php

namespace Modules\Policy\Entities;

use App\Traits\IconTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Policy\Database\factories\PolicyFileFactory;

class PolicyFile extends Model
{
    use HasFactory;

    use IconTrait;

    const FILE_PATH = 'policy-files';

    protected $appends = ['file_url', 'icon', 'file'];

    public function getFileUrlAttribute()
    {
        if($this->external_link){
            return str($this->external_link)->contains('http') ? $this->external_link : asset_url_local_s3($this->external_link);
        }

        return asset_url_local_s3(PolicyFile::FILE_PATH . '/' . $this->policy_id . '/' . $this->hashname);
    }


}
