@push('scripts')
    @include('sections.datatable_css')
@endpush

@php
    $addPermission = user()->permission('add_policy');
@endphp

@section('content')
    <!-- CONTENT WRAPPER START -->
    <div class="content-wrapper">
        <!-- Send Reminder Button Start -->
        <div class="d-grid d-lg-flex d-md-flex action-bar">
            <div id="table-actions" class="flex-grow-1 align-items-center mb-2 mb-lg-0 mb-md-0"></div>
            <div class="btn-group mt-2 mt-lg-0 mt-md-0 ml-0 ml-lg-3 ml-md-3" role="group">
                @if (!$policy->trashed() && (user()->is_admin || $addPermission == 'all') && $policy->status == 'published')
                    <x-forms.link-secondary link="javascript:;" class="float-right" icon="envelope"
                        id="send-reminder" data-policy-id="{{ $policy->id }}">@lang('app.send') @lang('policy::app.reminder')
                    </x-forms.link-secondary>
                @endif
            </div>
        </div>
        <!-- Send Reminder Button End -->

        <div class="mt-3 bg-white rounded d-flex flex-column w-tables">
            {!! $dataTable->table(['class' => 'table table-hover border-0 w-100']) !!}
        </div>
    </div>
    <!-- CONTENT WRAPPER END -->
@endsection


@push('scripts')
    @include('sections.datatable_js')

    <script>
        $('body').on('click', '#send-reminder', function() {
            const id = $(this).data('policy-id');
            let url = "{{ route('policy.send_remainder', ':id') }}";
            url = url.replace(':id', id);

            let token = "{{ csrf_token() }}";

            console.log(url);

            $.easyAjax({
                type: 'POST',
                url: url,
                data: {
                    '_token': token,
                },
                success: function(response) {
                    if (response.status == "success") {

                    }
                }
            });
        });
    </script>
@endpush
