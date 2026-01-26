@forelse($meetings as $meeting)
    <tr>
        <td class="text-left">
            {{ $meeting->start_date_time->translatedFormat(company()->date_format . ' - ' . company()->time_format) }}
        </td>
        <td class="text-left">
            {{ $meeting->end_date_time->translatedFormat(company()->date_format . ' - ' . company()->time_format) }}
        </td>
        <td class="text-left">
            <x-employee :user="$meeting->meetingFor" />
        </td>
        <td class="text-left">
            <x-employee :user="$meeting->meetingBy" />
        </td>
        <td class="text-right" width="5%">
            <a href="javascript:;" class="btn btn-secondary f-14 sendReminder" data-toggle="tooltip"
                data-meeting-id="{{ $meeting->id }}"
                data-original-title="@lang('modules.accountSettings.sendReminder')">
                <i class="fa fa-paper-plane mr-2"></i>
            </a>
        </td>
    </tr>
@empty
    <!-- No additional past meetings to load -->
@endforelse 