<div class="row">
    <div class="col-sm-12 col-lg-6 mt-3">
        <x-cards.data padding="false" otherClasses="h-200">
            <div class="d-flex justify-content-between align-items-center m-3">
                <h4>{{ __('onboarding::clan.menu.onboardingUsers') }}</h4>
                <h4>@lang('app.progress')</h4>
            </div>
            <x-table>
                @forelse ($onboardingCompletedUsers as $user)
                    @php
                        $completionPercent = $user->onboardingProgress;

                        if ($completionPercent <= 50) {
                            $statusColor = 'danger';
                        }
                        elseif ($completionPercent <= 80) {
                            $statusColor = 'warning';
                        }
                        else {
                            $statusColor = 'success';
                        }
                    @endphp
                    <tr>
                        <td class="pl-20">
                            <x-employee :user="$user" />
                        </td>
                        <td>
                            <div class="progress">
                                <div class="progress-bar f-12 bg-{{ $statusColor }}" role="progressbar"
                                style="width: {{ $user->onboardingProgress }}%;"
                                aria-valuenow="{{ $user->onboardingProgress }}" aria-valuemin="0"
                                aria-valuemax="100">{{ $user->onboardingProgress }}%</div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="shadow-none">
                            <x-cards.no-record icon="plane-departure" :message="__('onboarding::messages.noOnboardingCompletedUsers')" />
                        </td>
                    </tr>
                @endforelse
            </x-table>
        </x-cards.data>

    </div>
    <div class="col-sm-12 col-lg-6 mt-3">
        <x-cards.data padding="false" otherClasses="h-200">
            <div class="d-flex justify-content-between align-items-center m-3">
                <h4>{{ __('onboarding::clan.menu.offboardingUsers') }}</h4>
                <h4>@lang('app.progress')</h4>
            </div>
            <x-table>
                @forelse ($offboardingCompletedUsers as $user)
                    @php
                        $completionPercent = $user->offboardingProgress;
                        if($completionPercent <= 50) {
                            $statusColor = 'danger';
                        }
                        elseif ($completionPercent <= 80) {
                            $statusColor = 'warning';
                        }
                        else {
                            $statusColor = 'success';
                        }
                    @endphp
                    <tr>
                        <td class="pl-20">
                            <x-employee :user="$user" />
                        </td>

                        <td>
                            <div class="progress">
                                <div class="progress-bar f-12 bg-{{ $statusColor }}" role="progressbar"
                                    style="width: {{ $user->offboardingProgress }}%;"
                                    aria-valuenow="{{ $user->offboardingProgress }}" aria-valuemin="0"
                                    aria-valuemax="100">{{ $user->offboardingProgress }}%</div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="shadow-none">
                            <!-- Show message if no offboarding completed users -->
                            <x-cards.no-record icon="plane-departure" :message="__('onboarding::messages.noOffboardingCompletedUsers')" />
                        </td>
                    </tr>
                @endforelse
            </x-table>
        </x-cards.data>
    </div>
</div>
