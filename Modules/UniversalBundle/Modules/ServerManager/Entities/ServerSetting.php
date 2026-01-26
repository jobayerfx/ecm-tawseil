<?php

namespace Modules\ServerManager\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Company;
use App\Models\ModuleSetting;
use App\Scopes\CompanyScope;

class ServerSetting extends Model
{
    const MODULE_NAME = 'servermanager';

    protected $fillable = [
        'company_id',
        'setting_key',
        'setting_value',
        'setting_type',
        'description',
    ];

    /**
     * Get the company that owns the setting.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the setting value with proper casting.
     */
    public function getValueAttribute()
    {
        return match ($this->setting_type) {
            'integer' => (int) $this->setting_value,
            'boolean' => (bool) $this->setting_value,
            'json' => json_decode($this->setting_value, true),
            default => $this->setting_value,
        };
    }

    /**
     * Set the setting value with proper encoding.
     */
    public function setValueAttribute($value)
    {
        $this->setting_value = match ($this->setting_type) {
            'json' => json_encode($value),
            default => (string) $value,
        };
    }

    /**
     * Add module settings for a company
     */
    public static function addModuleSetting($company)
    {
        $roles = ['employee', 'admin'];
        ModuleSetting::createRoleSettingEntry(self::MODULE_NAME, $roles, $company);

    }

}
