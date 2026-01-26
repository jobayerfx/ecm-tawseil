<?php

namespace Modules\Onboarding\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateOnboardingDashboardRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */

    public function rules(): array
    {
        return [
            'completed_on' => 'required|date',
            'file' => 'nullable|file|max:10240',
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
