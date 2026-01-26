<?php

namespace Modules\Onboarding\Providers;

use App\Events\NewCompanyCreatedEvent;
use App\Events\NewUserEvent;
use App\Models\EmployeeDetails;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Onboarding\Entities\OnboardingTask;
use Modules\Onboarding\Listeners\CompanyCreatedListener;
use Modules\Onboarding\Events\NoticePeriodEvent;
use Modules\Onboarding\Events\OnboardingNotificationEvent;
use Modules\Onboarding\Listeners\OnboardingStartListener;
use Modules\Onboarding\Listeners\UserCreatedListener;
use Modules\Onboarding\Observers\NoticePeriodObserver;
use Modules\Onboarding\Observers\OnboardingCompanyObserver;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        NewUserEvent::class => [UserCreatedListener::class],
        NewCompanyCreatedEvent::class => [CompanyCreatedListener::class],
    ];

    protected $observers = [
        EmployeeDetails::class => [NoticePeriodObserver::class],
        OnboardingTask::class => [OnboardingCompanyObserver::class],
    ];
}
