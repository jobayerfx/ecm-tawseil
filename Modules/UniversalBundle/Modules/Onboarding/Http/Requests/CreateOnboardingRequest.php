<?php

namespace Modules\Onboarding\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateOnboardingRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */

    public function rules(): array
    {
        $onboardId = request()->onboarding_status_id;

        return [
            'title' => [
                'required',
                'unique:onboarding_tasks,title,' . $onboardId . ',id,company_id,' . company()->id,
            ],
            'task_for' => 'required|in:company,employee',
            'employee_can_see' => 'nullable|boolean',
            'type' => 'required|in:onboard,offboard',
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
