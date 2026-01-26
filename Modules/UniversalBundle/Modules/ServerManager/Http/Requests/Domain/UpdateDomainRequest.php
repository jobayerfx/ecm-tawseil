<?php

namespace Modules\ServerManager\Http\Requests\Domain;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class UpdateDomainRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $editPermission = user()->permission('edit_domain');
        return in_array($editPermission, ['all', 'added', 'owned', 'both']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $setting = company();
        return [
            'domain_name' => 'required|string|max:255',
            'domain_provider' => 'required|string|max:255',
            'provider_url' => 'nullable|url|max:255',
            // 'domain_type' => 'required|string|max:10',
            'registrar' => 'nullable|string|max:255',
            'registrar_url' => 'nullable|url|max:255',
            'registrar_username' => 'nullable|string|max:255',
            'registrar_password' => 'nullable|string|max:255',
            'registrar_status' => 'nullable|in:active,inactive,expired,suspended,transferred,pending',
            'registration_date' => 'required|date_format:"' . $setting->date_format . '"',
            'expiry_date' => 'required|date_format:"' . $setting->date_format . '"|after:registration_date',
            'renewal_date' => 'nullable|date_format:"' . $setting->date_format . '"|after:registration_date',
            'username' => 'nullable|string|max:255',
            'password' => 'nullable|string|max:255',
            'annual_cost' => 'nullable|numeric|min:0|max:999999.99',
            'billing_cycle' => 'nullable|string|max:50',
            'status' => 'required|in:active,inactive,expired,suspended,transferred,pending',
            'hosting_id' => [
                'nullable',
                Rule::exists('server_hostings', 'id')->where(function ($query) {
                    $query->where('company_id', company()->id);
                }),
            ],
            'project_id' => [
                'nullable',
                Rule::exists('projects', 'id')->where(function ($query) {
                    $query->where('company_id', company()->id);
                }),
            ],
            // The selected client id is invalid.
            'client_id' => [
                'nullable',
                'integer',
                function ($attribute, $value, $fail) {
                    $exists = DB::table('client_details')->where('user_id', $value)->exists();
                    if ($value !== null && !$exists) {
                        $fail(__('The selected client id is invalid.'));
                    }
                },
            ],
            'dns_provider' => 'nullable|string|max:255',
            'dns_status' => 'nullable|in:enabled,disabled',
            'nameservers' => 'nullable|string',
            'dns_records' => 'nullable|string',
            'whois_protection' => 'boolean',
            'auto_renewal' => 'boolean',
            'expiry_notification' => 'boolean',
            'notification_days_before' => 'nullable|integer|min:1|max:365',
            'notification_time_unit' => 'nullable|in:days,weeks,months',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'domain_name' => __('servermanager::validation.attributes.domain_name'),
            'domain_provider' => __('servermanager::validation.attributes.domain_provider'),
            'provider_url' => __('servermanager::validation.attributes.provider_url'),
            // 'domain_type' => __('servermanager::validation.attributes.domain_type'),
            'registrar' => __('servermanager::validation.attributes.registrar'),
            'registrar_url' => __('servermanager::validation.attributes.registrar_url'),
            'registrar_username' => __('servermanager::validation.attributes.registrar_username'),
            'registrar_password' => __('servermanager::validation.attributes.registrar_password'),
            'registrar_status' => __('servermanager::validation.attributes.registrar_status'),
            'registration_date' => __('servermanager::validation.attributes.purchase_date'),
            'expiry_date' => __('servermanager::validation.attributes.expiry_date'),
            'renewal_date' => __('servermanager::validation.attributes.renewal_date'),
            'username' => __('servermanager::validation.attributes.username'),
            'password' => __('servermanager::validation.attributes.password'),
            'annual_cost' => __('servermanager::validation.attributes.annual_cost'),
            'billing_cycle' => __('servermanager::validation.attributes.billing_cycle'),
            'status' => __('servermanager::validation.attributes.status'),
            'hosting_id' => __('servermanager::validation.attributes.hosting_id'),
            'project_id' => __('servermanager::validation.attributes.project_id'),
            'client_id' => __('servermanager::validation.attributes.client_id'),
            'dns_provider' => __('servermanager::validation.attributes.dns_provider'),
            'dns_status' => __('servermanager::validation.attributes.dns_status'),
            'nameservers' => __('servermanager::validation.attributes.nameservers'),
            'dns_records' => __('servermanager::validation.attributes.dns_records'),
            'whois_protection' => __('servermanager::validation.attributes.whois_protection'),
            'auto_renewal' => __('servermanager::validation.attributes.auto_renewal'),
            'expiry_notification' => __('servermanager::validation.attributes.expiry_notification'),
            'notification_days_before' => __('servermanager::validation.attributes.notification_days_before'),
            'notification_time_unit' => __('servermanager::validation.attributes.notification_time_unit'),
            'notes' => __('servermanager::validation.attributes.notes'),
        ];
    }
}
