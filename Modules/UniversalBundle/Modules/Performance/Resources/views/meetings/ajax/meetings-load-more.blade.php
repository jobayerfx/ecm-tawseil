@forelse($meetings as $date => $dateWiseMeetings)
    <!-- Month Section -->
    @if ($loop->first || \Carbon\Carbon::parse($date)->format('M') != \Carbon\Carbon::parse($prevDate ?? $date)->format('M'))
        <div class="mt-1 mb-4">
            <h4 class="mb-0 f-18 f-w-500 text-darkest-grey">{{ \Carbon\Carbon::parse($date)->format('F') }}</h4>
        </div>
    @endif

    <!-- Date Section -->
    <div class="date-section border" id="listViewDiv">
        <div class="d-flex">
            <!-- Date -->
            <div class="align-self-center text-center bg-white p-3 rounded border shadow-sm" id="dateDiv">
                <h3 class="mb-0 f-32 f-w-600 text-darkest-grey">{{ \Carbon\Carbon::parse($date)->format('d') }}</h3>
                <p class="mb-0 text-lightest f-16 f-w-500">{{ \Carbon\Carbon::parse($date)->format('D') }}</p>
            </div>
            <!-- Date wise meetings -->
            <div class="flex-grow-1 border-start ps-4">
                @foreach ($dateWiseMeetings as $meeting)
                    <div class="meeting-card bg-white border" data-meeting-date="{{ \Carbon\Carbon::parse($meeting->start_date_time)->format('Y-m-d') }}">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-4">
                                    <!-- Meeting Time -->
                                    @if (($activeTab == 'upcoming' && $meeting->status == 'pending') || ($activeTab == 'recurring' && $meeting->status == 'pending' && $meeting->start_date_time > \Carbon\Carbon::now()->setTimezone(company()->timezone)))
                                        <div class="d-flex align-items-center mt-1">
                                            <div class="f-14 mb-0 mr-3 text-dark bg-grey p-1 rounded"><i class="fa fa-clock mr-1"></i> @lang('performance::modules.startOn'): {{ $meeting->start_date_time->translatedFormat(company()->time_format) }}
                                            </div>
                                        </div>
                                    @else
                                        <div class="d-inline-flex align-items-center mb-3 f-14 text-dark bg-grey p-1 rounded">
                                            <i class="fa fa-clock mr-1"></i>
                                            {{ $meeting->start_date_time->translatedFormat(company()->time_format) }} -
                                            {{ $meeting->end_date_time->translatedFormat(company()->time_format) }}
                                        </div>
                                    @endif

                                    <!-- Status -->
                                    @if (($activeTab == 'upcoming' && $meeting->status == 'pending') || ($activeTab == 'recurring' && $meeting->status == 'pending' && $meeting->start_date_time > \Carbon\Carbon::now()->setTimezone(company()->timezone)))
                                        <div class="d-flex align-items-center mt-4">
                                            <div class="f-14 mb-0 mr-3 text-dark bg-grey p-1 pr-2 rounded"><i class="fa fa-clock mr-1"></i> @lang('performance::modules.endOn'): {{ $meeting->end_date_time->translatedFormat(company()->time_format) }}
                                            </div>
                                        </div>
                                    @else
                                        <div class="d-flex align-items-center">
                                            <p class="card-text f-14 text-dark-grey" id="statusDiv">
                                                @if ($meeting->status == 'pending')
                                                    <span class="badge badge-warning mt-3">
                                                        {{ ucfirst(__('performance::app.' . $meeting->status)) ?? __('performance::app.pending') }}
                                                    </span>
                                                @elseif($meeting->status == 'completed')
                                                    <span class="badge badge-success mt-3">
                                                        {{ ucfirst(__('performance::app.' . $meeting->status)) ?? __('performance::app.pending') }}
                                                    </span>
                                                @elseif($meeting->status == 'cancelled')
                                                    <span class="badge badge-danger mt-3">
                                                        {{ ucfirst(__('performance::app.' . $meeting->status)) ?? __('performance::app.pending') }}
                                                    </span>
                                                @else
                                                    <span class="badge badge-warning mt-3">
                                                        {{ ucfirst(__('performance::app.' . $meeting->status)) ?? '--' }}
                                                    </span>
                                                @endif
                                            </p>
                                        </div>
                                    @endif
                                </div>

                                <!-- Attendees -->
                                <div class="col-6">
                                    <div class="d-flex align-items-center">
                                        <div class="f-14 text-lightest mb-0 mr-3">@lang('performance::app.meetingFor'):</div>
                                        <div class="avatar-group">
                                            <x-employee :user="$meeting->meetingFor" />
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center mt-3">
                                        <div class="f-14 text-lightest mb-0 mr-3">@lang('performance::app.meetingBy'):</div>
                                        <div class="avatar-group">
                                            <x-employee :user="$meeting->meetingBy" />
                                        </div>
                                    </div>
                                </div>

                                <!-- Quick Actions -->
                                <div class="col-2 text-right">
                                    <div class="dropdown">
                                        <button class="btn btn-lg f-14 p-0 text-lightest text-capitalize rounded"
                                            type="button" data-toggle="dropdown" aria-haspopup="true"
                                            aria-expanded="false">
                                            <i class="fa fa-ellipsis-h"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right border-grey rounded b-shadow-4 p-0"
                                            aria-labelledby="dropdownMenuLink" tabindex="0">
                                            <a class="dropdown-item openRightModal"
                                                href="{{ route('meetings.show', $meeting->id) }}?tab=list">
                                                <i class="fa fa-eye mr-2"></i>@lang('app.view') @lang('performance::app.meeting')
                                            </a>

                                            @if ($meeting->status == 'pending' && \Carbon\Carbon::parse($meeting->start_date_time)->format('Y-m-d H:i:s') < \Carbon\Carbon::now()->setTimezone(company()->timezone)->format('Y-m-d H:i:s'))
                                                <a class="dropdown-item sendReminder" data-meeting-id="{{ $meeting->id }}" href="javascript:;">
                                                    <i class="fa fa-paper-plane mr-2"></i>@lang('modules.accountSettings.sendReminder')
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @php
        $prevDate = $date;
    @endphp
@empty
    <!-- No additional meetings to load -->
@endforelse