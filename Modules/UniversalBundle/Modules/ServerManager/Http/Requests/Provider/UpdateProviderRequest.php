<?php

namespace Modules\ServerManager\Http\Requests\Provider;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProviderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $editPermission = user()->permission('edit_provider');
        return in_array($editPermission, ['all', 'added', 'owned', 'both']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'url' => 'required|url|max:255',
            'type' => 'required|in:domain,hosting,both',
            'description' => 'nullable|string|max:1000',
            'status' => 'required|in:active,inactive',
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
            'url' => __('servermanager::validation.attributes.provider_url'),
            'type' => __('servermanager::validation.attributes.provider_type'),
            'description' => __('servermanager::validation.attributes.description'),
            'status' => __('servermanager::validation.attributes.status'),
        ];
    }

}
