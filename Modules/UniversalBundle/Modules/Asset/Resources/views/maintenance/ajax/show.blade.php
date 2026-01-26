<div class="row">
    <div class="col-sm-12">
        <x-cards.data :title="__('asset::app.maintenanceDetails')">
            <x-cards.data-row :label="__('asset::app.assetName')" :value="$maintenance->asset->name" />
            <x-cards.data-row :label="__('asset::app.maintenanceType')" :value="__('asset::app.' . $maintenance->type)" />
            <x-cards.data-row :label="__('asset::app.maintenanceTitle')" :value="$maintenance->title" />
            <x-cards.data-row :label="__('asset::app.description')" :value="$maintenance->description" />
            <x-cards.data-row :label="__('asset::app.scheduledDate')" :value="$maintenance->scheduled_date->translatedFormat(company()->date_format)" />
            @if($maintenance->due_date)
                <x-cards.data-row :label="__('asset::app.dueDate')" :value="$maintenance->due_date->translatedFormat(company()->date_format)" />
            @endif
            @if($maintenance->started_at)
                <x-cards.data-row :label="__('asset::app.startedDate')" :value="$maintenance->started_at->translatedFormat(company()->date_format)" />
            @endif
            @if($maintenance->completed_at)
                <x-cards.data-row :label="__('asset::app.completedDate')" :value="$maintenance->completed_at->translatedFormat(company()->date_format)" />
            @endif
            <x-cards.data-row :label="__('asset::app.status')" :value="__('asset::app.' . $maintenance->status)" />
            @if($maintenance->assignedTo)
                <x-cards.data-row :label="__('asset::app.assignedTo')">
                    <x-slot name="value">
                        @include('components.employee', ['user' => $maintenance->assignedTo])
                    </x-slot>
                </x-cards.data-row>
            @endif
            @if($maintenance->notes)
                <x-cards.data-row :label="__('asset::app.notes')" :value="$maintenance->notes" />
            @endif
            @if($maintenance->completion_notes)
                <x-cards.data-row :label="__('asset::app.completionNotes')" :value="$maintenance->completion_notes" />
            @endif
        </x-cards.data>
    </div>
</div>

