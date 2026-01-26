@if (in_array(\Modules\Onboarding\Entities\OnboardingSetting::MODULE_NAME, user_modules()))
    <x-onboarding::start-onboarding :employee="$employee"/>
@endif
