@if (in_array(\Modules\Onboarding\Entities\OnboardingSetting::MODULE_NAME, user_modules()))
<div class="col-sm-12">
    <x-onboarding::employee-onboarding :employee="$user" :type="'dashboard'"/>
    <x-onboarding::employee-offboarding :employee="$user" :type="'dashboard'"/>
</div>
@endif
