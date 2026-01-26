<?php

namespace Modules\GroupMessage\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreNewGroupRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $id = request()->id;

        return [
            'name' => [
                'required',
                'unique:groups,name,'.$id.',id,company_id,' . company()->id,
            ],
            'members.0' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'members.0.required' => __('groupmessage::messages.atleastOneMember')
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

}
