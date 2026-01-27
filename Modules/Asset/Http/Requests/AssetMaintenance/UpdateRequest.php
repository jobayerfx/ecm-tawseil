<?php

namespace Modules\Asset\Http\Requests\AssetMaintenance;

use App\Http\Requests\CoreRequest;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends CoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'asset_id' => 'required|exists:assets,id',
            'type' => 'required|in:planned,reactive',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'scheduled_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:scheduled_date',
            'assigned_to' => 'nullable|exists:users,id',
            'status' => 'required|in:scheduled,inprogress,completed,overdue,cancelled',
            'notes' => 'nullable|string',
            'completion_notes' => 'nullable|string',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'asset_id.required' => __('asset::app.assetName') . ' ' . __('app.required'),
            'asset_id.exists' => __('asset::app.assetName') . ' ' . __('app.invalid'),
            'type.required' => __('asset::app.maintenanceType') . ' ' . __('app.required'),
            'type.in' => __('asset::app.maintenanceType') . ' ' . __('app.invalid'),
            'title.required' => __('asset::app.maintenanceTitle') . ' ' . __('app.required'),
            'scheduled_date.required' => __('asset::app.scheduledDate') . ' ' . __('app.required'),
            'scheduled_date.date' => __('asset::app.scheduledDate') . ' ' . __('app.invalid'),
            'due_date.date' => __('asset::app.dueDate') . ' ' . __('app.invalid'),
            'due_date.after_or_equal' => __('asset::app.dueDate') . ' ' . __('app.mustBeAfterOrEqual') . ' ' . __('asset::app.scheduledDate'),
            'status.required' => __('asset::app.status') . ' ' . __('app.required'),
            'status.in' => __('asset::app.status') . ' ' . __('app.invalid'),
            'assigned_to.exists' => __('asset::app.assignedTo') . ' ' . __('app.invalid'),
        ];
    }
}

