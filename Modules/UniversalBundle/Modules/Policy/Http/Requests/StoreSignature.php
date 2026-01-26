<?php

namespace Modules\Policy\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSignature extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     */

    public function rules(): array
    {
        $rules = [];

        if(!request()->has('signature'))
        {
            $rules = [
                'image' => 'required|mimes:jpg,png,jpeg',

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
