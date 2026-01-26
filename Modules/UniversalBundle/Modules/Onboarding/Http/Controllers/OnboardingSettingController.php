<?php

namespace Modules\Onboarding\Http\Controllers;

use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Modules\Onboarding\Entities\OnboardingCompletedTask;
use Modules\Onboarding\Entities\OnboardingNotificationSetting;
use Modules\Onboarding\Entities\OnboardingTask;
use Modules\Onboarding\Http\Requests\CreateOnboardingRequest;

class OnboardingSettingController extends AccountBaseController
{
    /**
     * Display a listing of the resource.
     */

    public function __construct()
    {
         parent::__construct();
         $this->pageTitle = 'onboarding::clan.menu.onboardingSettings';
         $this->activeSettingMenu = 'onboarding_settings';
         $this->middleware(function ($request, $next) {

             return $next($request);
         });

    }

    public function index()
    {

        $tab = request('tab');

        $companyId = company()->id;

        $viewonboardingPermission = user()->permission('manage_employee_onboarding');
        $viewoffboardingPermission = user()->permission('manage_employee_offboarding');

        abort_403(!($viewonboardingPermission == 'all' || $viewoffboardingPermission == 'all'));
        $this->emailSettings = OnboardingNotificationSetting::all();
        $this->activeTab = $tab ?: 'onboarding';
        $onboardingTasks = OnboardingTask::where('company_id', $companyId)->get();

        switch ($tab) {

        case 'offboarding':

            $this->onboardingSetting = $onboardingTasks->filter(function ($value) {
                return $value->type == 'offboard';
            });

            $this->view = 'onboarding::onboarding-settings.ajax.offboarding';
            break;

        case 'onboard-notification-setting':
            $this->view = 'onboarding::onboarding-settings.ajax.onboard-notification-setting';
            break;

        default:
            $this->onboardingSetting = $onboardingTasks->filter(function ($value) {
                return $value->type == 'onboard';
            });

            $this->view = 'onboarding::onboarding-settings.ajax.onboarding';
            break;
        }

        if (request()->ajax()) {
            $html = view($this->view, $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle, 'activeTab' => $this->activeTab]);
        }

        return view('onboarding::onboarding-settings.index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if(request()->type == 'onboarding')
        {
            return view('onboarding::onboarding-settings.create-onboarding-settings-modal', $this->data);
        }

        return view('onboarding::onboarding-settings.create-offboarding-settings-modal', $this->data);

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateOnboardingRequest $request)
    {
        $onboardingSetting = new OnboardingTask();

        // Determine the value of 'employee_can_see' based on 'task_for'
        $taskFor = $request->input('task_for');
        $employeeCanSee = 0;

        if ($taskFor === 'employee') {
            $employeeCanSee = 1;
        } elseif ($taskFor === 'company' && $request->has('employee_can_see')) {
            $employeeCanSee = 1;
        }

        $onboardingSetting->fill([
            'title' => $request->input('title'),
            'task_for' => $taskFor,
            'employee_can_see' => $employeeCanSee,
            'type' => $request->input('type') === 'offboard' ? 'offboard' : 'onboard',
        ]);

        // Save the onboarding task
        $onboardingSetting->save();

        return Reply::success(__('messages.recordSaved'));
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('onboarding::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        // Retrieve the existing onboarding setting
        $onboardingSetting = OnboardingTask::where('company_id', company()->id)->findOrFail($id);

        if(request()->type == 'onboarding')
        {
            // Pass the onboarding setting data to the view
            return view('onboarding::onboarding-settings.edit-onboarding-settings-modal', ['onboardingSetting' => $onboardingSetting]);
        }

        // Pass the onboarding setting data to the view
        return view('onboarding::onboarding-settings.edit-offboarding-settings-modal', ['onboardingSetting' => $onboardingSetting]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CreateOnboardingRequest $request, $id)
    {
        $onboardingSetting = OnboardingTask::where('company_id', company()->id)->findOrFail($id);

        // Determine the type and task_for values directly from the request
        $type = $request->input('type') === 'offboard' ? 'offboard' : 'onboard';
        $taskFor = $request->input('task_for') === 'employee' ? 'employee' : 'company';

        // Determine the value of 'employee_can_see' based on 'task_for'
        $employeeCanSee = 0;
        if ($taskFor === 'employee') {
            $employeeCanSee = 1;
        } elseif ($taskFor === 'company' && $request->has('employee_can_see')) {
            $employeeCanSee = 1;
        }

        // Update the onboarding task with the values from the request
        $onboardingSetting->update([
        'title' => $request->input('title'),
        'task_for' => $taskFor,
        'employee_can_see' => $employeeCanSee,
        'type' => $type,
        ]);

        return Reply::success(__('messages.updateSuccess'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {

        OnboardingTask::destroy($id);

        return Reply::success(__('messages.deleteSuccess'));
    }

    public function addPriority()
    {
        $order = request()->order;

        foreach ($order as $index => $id)
        {
            OnboardingTask::where('company_id', company()->id)->where('id', $id)->update(['column_priority' => $index + 1]);
        }

        return Reply::success(__('messages.updateSuccess'));

    }

    public function updateNotification()
    {
        $order = request()->order;

        foreach ($order as $index => $id)
        {
            OnboardingTask::where('company_id', company()->id)->where('id', $id)->update(['column_priority' => $index + 1]);
        }

        return Reply::success(__('messages.updateSuccess'));

    }

}
