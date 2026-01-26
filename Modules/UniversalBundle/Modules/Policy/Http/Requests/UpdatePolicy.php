<?php

namespace Modules\Policy\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePolicy extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $setting = company();

        $rules = [
            'title' => 'required',
            'date' => 'required|date_format:"' . $setting->date_format . '"',
        ];

        if (request()->status == 'draft' && request()->file == 'file') {
            $rules = [
                'description' => 'required_without:file',
            ];
        }

        return $rules;
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
