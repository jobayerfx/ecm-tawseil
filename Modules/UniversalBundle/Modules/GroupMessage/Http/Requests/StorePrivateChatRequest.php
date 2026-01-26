<?php

namespace Modules\GroupMessage\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePrivateChatRequest extends FormRequest
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
            'user_id' => 'required_if:user_type,employee',
            'client_id' => 'required_if:user_type,client',
        ];

        if($this->types == 'modal'){
            $rules['message'] = 'required';
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'user_id.required_if' => __('groupmessage::validation.selectUserToSendMessage'),
            'client_id.required_if' => __('groupmessage::validation.selectClientToSendMessage'),
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
