<div class="modal-header">
    <h5 class="modal-title"><i class="fa fa-list mr-2"></i> @lang('performance::app.pastMeetings')</h4>
    </h5>
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
</div>

<x-form id="save-discussion-form">
    <div class="modal-body">
        <div class="portlet-body"></div>
        <div class="row">
            <x-table class="my-3 rounded" id="past-meetings-table">
                <x-slot name="thead">
                    <tr>
                        <th class="text-left">@lang('performance::modules.startOn')</th>
                        <th class="text-left">@lang('performance::modules.endOn')</th>
                        <th class="text-left">@lang('performance::app.meetingFor')</th>
                        <th class="text-left">@lang('performance::app.meetingBy')</th>
                        <th class="text-right pr-3" width="5%">@lang('app.action')</th>
                    </tr>
                </x-slot>

                @foreach ($meetings as $date => $dayMeetings)
                    @foreach ($dayMeetings as $key => $meeting)
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
                    @endforeach
                @endforeach
            </x-table>

            @if (count($meetings) <= 0)
                <x-cards.no-record icon="redo" :message="__('performance::messages.meetingsNotFound')" />
            @endif

            <!-- Load More Button for Past Meetings -->
            @if(isset($hasMorePastMeetings) && $hasMorePastMeetings)
                <div class="d-flex justify-content-center mt-4">
                    <button class="btn btn-secondary f-14 load-more-past-meetings"
                            data-skip="{{ $currentSkip ?? 0 }}"
                            data-total="{{ $totalPastMeetings ?? 0 }}"
                            data-status="{{ request()->status ?? 'pending' }}">
                        <i class="fa fa-spinner fa-spin d-none"></i>
                        <span class="load-more-text">@lang('performance::app.loadMore')</span>
                    </button>
                </div>
            @endif
        </div>
    </div>
    <div class="modal-footer">
        <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.close')</x-forms.button-cancel>
    </div>
</x-form>

<script>
    $('.sendReminder').click(function() {
        let meetingId = $(this).data('meeting-id');
        var url = "{{ route('meetings.send_reminder', ':id') }}";
        url = url.replace(':id', meetingId);

        if (url) {
            $.easyAjax({
                url: url,
                type: "GET",
                buttonSelector: $(this),
                blockUI: true,
                disableButton: true,
                success: function(response) {
                    if (response.status == "success") {
                        $.easyUnblockUI();
                    }
                }
            });
        }
    });

    // Load More Functionality for Past Meetings
    $('body').on('click', '.load-more-past-meetings', function() {
        var button = $(this);
        var skip = parseInt(button.data('skip')) + 10; // Add 10 more meetings
        var status = button.data('status');
        var employee = '{{ request()->employee ?? "all" }}';
        var month = '{{ request()->month ?? "" }}';
        var year = '{{ request()->year ?? "" }}';
        var searchText = '{{ request()->searchText ?? "" }}';

        // Show loading state
        button.find('.fa-spinner').removeClass('d-none');
        button.find('.load-more-text').text('@lang("app.loading")...');
        button.prop('disabled', true);

        $.easyAjax({
            url: "{{ route('meetings.load_more_past') }}",
            type: "GET",
            data: {
                status: status,
                employee: employee,
                month: month,
                year: year,
                searchText: searchText,
                skip: skip
            },
            success: function(response) {
                if (response.status == "success") {
                    if (response.html.trim() !== '') {
                        // Append the new content to the table body
                        var tableBody = $('#past-meetings-table tbody');
                        tableBody.append(response.html);

                        // Move the load more button to the bottom
                        var loadMoreContainer = button.closest('.d-flex');
                        $('#past-meetings-table').after(loadMoreContainer);

                        // Update button data
                        button.data('skip', skip);

                        // Hide button if no more meetings
                        if (!response.hasMorePastMeetings) {
                            loadMoreContainer.remove();
                        } else {
                            // Reset button state
                            button.find('.fa-spinner').addClass('d-none');
                            button.find('.load-more-text').text('@lang("performance::app.loadMore")');
                            button.prop('disabled', false);
                        }
                    } else {
                        // No more meetings to load
                        button.closest('.d-flex').remove();
                        button.find('.fa-spinner').addClass('d-none');
                        button.find('.load-more-text').text('@lang("performance::app.loadMore")');
                        button.prop('disabled', false);
                    }
                }
            },
            error: function() {
                // Reset button state on error
                button.find('.fa-spinner').addClass('d-none');
                button.find('.load-more-text').text('@lang("performance::app.loadMore")');
                button.prop('disabled', false);
            }
        });
    });
</script>
