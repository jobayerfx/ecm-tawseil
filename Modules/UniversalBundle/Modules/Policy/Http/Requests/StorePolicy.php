<?php

namespace Modules\Policy\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePolicy extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        $setting = company();

        $rules = [
            'title' => 'required',
            'date' => 'required|date_format:"' . $setting->date_format . '"',
            'description' => 'required_without:file',
            'file' => 'required_without:description'
        ];
        return $rules;

    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize()
    {
        return true;
    }
}
