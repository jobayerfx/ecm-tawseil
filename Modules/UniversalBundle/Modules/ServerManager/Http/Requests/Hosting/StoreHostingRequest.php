<?php

namespace Modules\ServerManager\Http\Requests\Hosting;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreHostingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $addPermission = user()->permission('add_hosting');
        return in_array($addPermission, ['all', 'added']);
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
            'name' => 'required|string|max:255',
            // 'domain_name' => 'required|string|max:255',
            'hosting_provider' => 'required|integer|exists:server_providers,id',
            'server_type' => 'required|integer|exists:server_types,id',
            'server_location' => 'nullable|string|max:255',
            'ip_address' => 'nullable|ip',
            'purchase_date' => 'required|date_format:"' . $setting->date_format . '"',
            'renewal_date' => 'required|date_format:"' . $setting->date_format . '"|after:purchase_date',
            'annual_cost' => 'nullable|numeric|min:0|max:999999.99',
            'billing_cycle' => 'required',
            'status' => 'required|in:active,inactive,expired,suspended,cancelled,pending',
            'disk_space' => 'nullable|string|max:100',
            'bandwidth' => 'nullable|string|max:100',
            'ssl_certificate' => 'boolean',
            'backup_enabled' => 'boolean',
            'control_panel' => 'nullable|string|max:100',
            'cpanel_url' => 'nullable|url|max:255',
            'username' => 'nullable|string|max:255',
            'password' => 'nullable|string|max:255',
            'project' => 'nullable|integer|exists:projects,id',
            'client' => 'nullable|integer|exists:client_details,id',
            'ftp_username' => 'nullable|string|max:255',
            'ftp_password' => 'nullable|string|max:255',
            'database_limit' => 'nullable|integer|min:0',
            'email_limit' => 'nullable|integer|min:0',
            // 'assigned_to' => [
            //     'nullable',
            //     Rule::exists('users', 'id')->where(function ($query) {
            //         $query->where('company_id', company()->id);
            //     }),
            // ],
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
            'name' => __('servermanager::validation.attributes.name'),
            // 'domain_name' => __('servermanager::validation.attributes.domain_name'),
            'hosting_provider' => __('servermanager::validation.attributes.hosting_provider'),
            'server_type' => __('servermanager::validation.attributes.server_type'),
            'server_location' => __('servermanager::validation.attributes.server_location'),
            'ip_address' => __('servermanager::validation.attributes.ip_address'),
            'purchase_date' => __('servermanager::validation.attributes.purchase_date'),
            'renewal_date' => __('servermanager::validation.attributes.renewal_date'),
            'annual_cost' => __('servermanager::validation.attributes.annual_cost'),
            'billing_cycle' => __('servermanager::validation.attributes.billing_cycle'),
            'status' => __('servermanager::validation.attributes.status'),
            'disk_space' => __('servermanager::validation.attributes.disk_space'),
            'bandwidth' => __('servermanager::validation.attributes.bandwidth'),
            'ssl_certificate' => __('servermanager::validation.attributes.ssl_certificate'),
            'backup_enabled' => __('servermanager::validation.attributes.backup_enabled'),
            'control_panel' => __('servermanager::validation.attributes.control_panel'),
            'cpanel_url' => __('servermanager::validation.attributes.cpanel_url'),
            'username' => __('servermanager::validation.attributes.username'),
            'password' => __('servermanager::validation.attributes.password'),
            'project' => __('servermanager::validation.attributes.project'),
            'client' => __('servermanager::validation.attributes.client'),
            'ftp_username' => __('servermanager::validation.attributes.ftp_username'),
            'ftp_password' => __('servermanager::validation.attributes.ftp_password'),
            'database_limit' => __('servermanager::validation.attributes.database_limit'),
            'email_limit' => __('servermanager::validation.attributes.email_limit'),
            // 'assigned_to' => __('servermanager::validation.attributes.assigned_to'),
            'notes' => __('servermanager::validation.attributes.notes'),
        ];
    }
}
