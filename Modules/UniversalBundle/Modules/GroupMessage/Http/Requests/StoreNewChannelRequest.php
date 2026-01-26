<?php

namespace Modules\GroupMessage\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreNewChannelRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $id = request()->channel_id;

        return [
            'name' => [
                'required',
                'unique:channels,name,'.$id.',id,company_id,' . company()->id,
            ],
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
