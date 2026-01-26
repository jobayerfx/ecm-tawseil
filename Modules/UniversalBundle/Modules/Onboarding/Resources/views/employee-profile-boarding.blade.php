@if (in_array(\Modules\Onboarding\Entities\OnboardingSetting::MODULE_NAME, user_modules()))
    <x-onboarding::employee-onboarding :employee="$employee" :type="'profile'" />
    <x-onboarding::employee-offboarding :employee="$employee" :type="'profile'" />
@endif
