<?php

namespace Modules\Onboarding\Views\Components;

use App\Models\User;
use Illuminate\View\Component;
use Modules\Onboarding\Entities\OnboardingCompletedTask;
use App\Scopes\ActiveScope;
class BoardingUsers extends Component
{

    public $onboardingCompletedUsers;
    public $offboardingCompletedUsers;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct()
    {
        $onboardTasks = OnboardingCompletedTask::whereHas('onboardingTask', function ($query) {
            $query->where('type', 'onboard');
        })->get();

        $offboardTasks = OnboardingCompletedTask::whereHas('onboardingTask', function ($query) {
            $query->where('type', 'offboard');
        })->get();

        $allOnBoardUsers = $onboardTasks->pluck('employee_id')->unique();
        $allOffBoardUsers = $offboardTasks->pluck('employee_id')->unique();
        $allBoardingUsers = $allOnBoardUsers->merge($allOffBoardUsers)->unique();

        $allUsers = User::whereIn('id', $allBoardingUsers)->with('employeeDetail')
            ->whereHas('employeeDetail', function ($query) {
                $query->where('onboard_completed', 0)
                    ->orWhere('offboard_completed', 0);
            })->withoutGlobalScope(ActiveScope::class)->get();

        $allUsers->each(function ($user) {
            $totalOnboardingTasks = OnboardingCompletedTask::whereHas('onboardingTask', function ($query) {
                $query->where('type', 'onboard');
            })->where('employee_id', $user->id)->count();

            $onboardCompleted = OnboardingCompletedTask::whereHas('onboardingTask', function ($query) {
                $query->where('type', 'onboard');
            })->where('employee_id', $user->id)->where('status', 'completed')->count();

            $totalOffboardingTasks = OnboardingCompletedTask::whereHas('onboardingTask', function ($query) {
                $query->where('type', 'offboard');
            })->where('employee_id', $user->id)->count();

            $offboardCompleted = OnboardingCompletedTask::whereHas('onboardingTask', function ($query) {
                $query->where('type', 'offboard');
            })->where('employee_id', $user->id)->where('status', 'completed')->count();

            if ($user->employeeDetail && $user->employeeDetail->onboard_completed == 0) {
                $onboardingProgress = $totalOnboardingTasks > 0 ? round(($onboardCompleted / $totalOnboardingTasks) * 100, 2) : 0;
                $user->onboardingProgress = $onboardingProgress;
            }

            if ($user->employeeDetail && $user->employeeDetail->offboard_completed == 0) {
                $offboardingProgress = $totalOffboardingTasks > 0 ? round(($offboardCompleted / $totalOffboardingTasks) * 100, 2) : 0;
                $user->offboardingProgress = $offboardingProgress;
            }
        });

        // Split the completed users into onboarding and offboarding users
        $this->onboardingCompletedUsers = $allUsers->filter(function ($user) {

            $totalOnboardingTasks = OnboardingCompletedTask::whereHas('onboardingTask', function ($query) {
                $query->where('type', 'onboard');
            })->where('employee_id', $user->id)->count();

            return $totalOnboardingTasks > 0 && $user->employeeDetail && $user->employeeDetail->onboard_completed == 0;
        });

        $this->offboardingCompletedUsers = $allUsers->filter(function ($user) {

            $totalOffboardingTasks = OnboardingCompletedTask::whereHas('onboardingTask', function ($query) {
                $query->where('type', 'offboard');
            })->where('employee_id', $user->id)->count();

            return $totalOffboardingTasks > 0 && $user->employeeDetail && $user->employeeDetail->offboard_completed == 0;
        });
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|string
     */
    public function render()
    {
        return view('onboarding::components.boarding-users', [
            'onboardingCompletedUsers' => $this->onboardingCompletedUsers,
            'offboardingCompletedUsers' => $this->offboardingCompletedUsers,
        ]);
    }

}
