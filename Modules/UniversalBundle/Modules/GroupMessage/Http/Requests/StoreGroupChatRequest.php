<?php

namespace Modules\GroupMessage\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGroupChatRequest extends FormRequest
{

    public function prepareForValidation()
    {
        $this->merge([
            'message' => trim_editor($this->message),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */

    public function rules()
    {

        $rules = [
            'group_id' => 'required_if:user_type,group',
        ];

        if($this->types == 'modal'){
            $rules['message'] = 'required';
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'group_id.required_if' => __('groupmessage::validation.selectGroupToSendMessage'),
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
