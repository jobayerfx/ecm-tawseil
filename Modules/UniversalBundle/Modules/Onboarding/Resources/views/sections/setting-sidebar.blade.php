@php
    $viewonboardingPermission = user()->permission('manage_employee_onboarding');
    $viewoffboardingPermission = user()->permission('manage_employee_offboarding');
@endphp

@if (module_enabled('Onboarding') && in_array('onboarding', user_modules()))
    @if ($viewonboardingPermission != 'none' && $viewoffboardingPermission != 'none')
        <x-setting-menu-item :active="$activeMenu" menu="onboarding_settings" :href="route('onboarding-settings.index', ['tab' => 'onboarding'])" :text="__('onboarding::clan.menu.onOffboardingSettings')" />
    @elseif ($viewonboardingPermission != 'none')
        <x-setting-menu-item :active="$activeMenu" menu="onboarding_settings" :href="route('onboarding-settings.index', ['tab' => 'onboarding'])" :text="__('onboarding::clan.menu.onboardingSettings')" />
    @elseif ($viewoffboardingPermission != 'none')
            <x-setting-menu-item :active="$activeMenu" menu="onboarding_settings" :href="route('onboarding-settings.index', ['tab' => 'offboarding'])" :text="__('onboarding::clan.menu.offboardingSettings')" />
    @endif
@endif
