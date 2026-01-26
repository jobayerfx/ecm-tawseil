<?php

namespace Modules\GroupMessage\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreChannelChatRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [
            'channel_id' => 'required_if:user_type,channel',
        ];

        if($this->types == 'modal'){
            $rules['message'] = 'required';
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'channel_id.required_if' => __('groupmessage::validation.selectChannelToSendMessage'),
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
