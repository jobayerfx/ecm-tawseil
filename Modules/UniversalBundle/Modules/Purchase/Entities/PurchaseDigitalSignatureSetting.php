<?php

namespace Modules\Purchase\Entities;

use App\Models\BaseModel;
use App\Traits\HasCompany;

class PurchaseDigitalSignatureSetting extends BaseModel
{

    use HasCompany;

    protected $guarded = ['id'];
    protected $table = 'purchase_digital_signature_setting';

    protected $appends = ['authorised_signature_url'];

    public function getAuthorisedSignatureUrlAttribute()
    {
        return (is_null($this->signature)) ? '' : asset_url_local_s3('app-logo/' . $this->signature);
    }
}
