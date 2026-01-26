@extends('layouts.app')

@section('content')
    @php
        $viewonboardingPermission = user()->permission('manage_employee_onboarding');
        $viewoffboardingPermission = user()->permission('manage_employee_offboarding');

    @endphp
    <!-- SETTINGS START -->
    <div class="w-100 d-flex ">

        @include('sections.setting-sidebar')

        <x-setting-card>
            <x-slot name="header">
                <div class="s-b-n-header" id="tabs">
                    <nav class="tabs px-4 border-bottom-grey">
                        <div class="nav" id="nav-tab" role="tablist">

                            @if ($viewonboardingPermission == 'all')
                                <a class="nav-item nav-link f-15 onboarding"
                                    href="{{ route('onboarding-settings.index') }}?tab=onboarding" role="tab"
                                    aria-controls="nav-onboarding" aria-selected="true">@lang('onboarding::clan.menu.onboardingSettings')
                                </a>
                            @endif

                            @if ($viewoffboardingPermission == 'all')
                            <a class="nav-item nav-link f-15 offboarding"
                                href="{{ route('onboarding-settings.index') }}?tab=offboarding" role="tab"
                                aria-controls="nav-offboarding" aria-selected="true">@lang('onboarding::clan.menu.offboardingSettings')
                            </a>
                            @endif

                            @if ($viewoffboardingPermission == 'all' && $viewonboardingPermission == 'all')
                            <a class="nav-item nav-link f-15 onboard-notification-setting"
                                href="{{ route('onboarding-settings.index') }}?tab=onboard-notification-setting" role="tab"
                                aria-controls="nav-offboarding" aria-selected="true">@lang('onboarding::clan.menu.notificationSetting')
                            </a>
                            @endif

                        </div>
                    </nav>
                </div>
            </x-slot>

            <x-slot name="buttons">
                <div class="row">

                    <div class="col-md-12 mb-2">

                        @if ($viewonboardingPermission == 'all')
                            <x-forms.button-primary icon="plus" id="add-onboarding"
                                class="onboarding-btn mb-2 actionBtn d-none">
                                @lang('onboarding::clan.menu.addOnboardingTask')
                            </x-forms.button-primary>
                        @endif
                        @if ($viewoffboardingPermission == 'all')

                        <x-forms.button-primary icon="plus" id="add-offboarding"
                            class="offboarding-btn d-none mb-2 actionBtn"> @lang('onboarding::clan.menu.addOffboardingTask')
                        </x-forms.button-primary>
                        @endif
                    </div>

                </div>
            </x-slot>

            @include($view)

        </x-setting-card>

    </div>
    <!-- SETTINGS END -->
@endsection

@push('scripts')
    <script>
        $('.nav-item').removeClass('active');
        const activeTab = "{{ $activeTab }}";
        $('.' + activeTab).addClass('active');

        function showBtn(activeTab) {
            $('.actionBtn').addClass('d-none');
            $('.' + activeTab + '-btn').removeClass('d-none');
        }

        $('#add-onboarding').click(function() {
            var url = "{{ route('onboarding-settings.create') }}?type=onboarding";
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        $('#add-offboarding').click(function() {
            var url = "{{ route('onboarding-settings.create') }}?type=offboarding";
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        showBtn(activeTab);
    </script>
@endpush
