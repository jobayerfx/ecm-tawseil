<?php

namespace Modules\Policy\Http\Controllers;

use Carbon\Carbon;
use App\Models\Team;
use App\Helper\Files;
use App\Helper\Reply;
use Barryvdh\DomPDF\PDF;
use App\Models\Designation;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Webklex\PDFMerger\PDFMerger;
use Illuminate\Support\Facades\App;
use Modules\Policy\Entities\Policy;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Modules\Policy\Http\Requests\StorePolicy;
use Modules\Policy\DataTables\PolicyDataTable;
use Modules\Policy\Http\Requests\UpdatePolicy;
use App\Http\Controllers\AccountBaseController;
use App\Models\StorageSetting;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Modules\Policy\DataTables\AcknowledgedDataTable;
use Modules\Policy\DataTables\ArchivePolicyDataTable;
use Modules\Policy\DataTables\NonAcknowledgedDataTable;
use Modules\Policy\Entities\PolicyEmployeeAcknowledged;
use Modules\Policy\Events\PolicyAcknowledgedEvent;
use Modules\Policy\Events\PolicyPublishedEvent;
use Modules\Policy\Events\SendReminderEvent;
use Modules\Policy\Http\Requests\StoreSignature;
use App\Models\Notification;
use App\Models\EmployeeDetails;

class PolicyController extends AccountBaseController
{
    /**
     * Display a listing of the resource.
     */

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'policy::app.policyCenter';

        $this->middleware(function ($request, $next) {

            abort_403(!in_array('policy', $this->user->modules));
            return $next($request);
        });
    }

    public function index(PolicyDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_policy');

        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both']));

        $this->pageTitle = 'policy::app.policyCenter';
        $this->teams = Team::all();
        $this->designations = Designation::allDesignations();

        return $dataTable->render('policy::policy.index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $addPermission = user()->permission('add_policy');
        abort_403(!in_array($addPermission, ['all']));

        $this->pageTitle = __('policy::app.addPolicy');
        $this->view = 'policy::policy.ajax.create';

        $this->teams = Team::all();
        $this->designations = Designation::allDesignations();

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('policy::policy.create', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePolicy $request)
    {
        $addPermission = user()->permission('add_policy');
        abort_403(!in_array($addPermission, ['all']));

        DB::beginTransaction();

        $policy = new Policy();
        $date = (!is_null($request->date)) ? Carbon::createFromFormat($this->company->date_format, $request->date)->format('Y-m-d') : null;
        $policy->title = $request->title;
        $policy->company_id = company()->id;
        $policy->description = $request->description;
        $policy->date  = $date;
        $policy->gender  = !is_null($request->gender) ? $request->gender : null;
        $policy->department_id_json = $request->department ? json_encode($request->department) : null;
        $policy->designation_id_json = $request->designation ? json_encode($request->designation) : null;
        $policy->employment_type_json = $request->employment_type ? json_encode($request->employment_type) : null;
        $policy->signature_required = $request->signature_required == 'yes' ? 'yes' : 'no';
        $policy->added_by = user()->id;

        if (request()->hasFile('file')) {
            Files::deleteFile($policy->filename, Policy::FILE_PATH);
            $policy->filename = Files::uploadLocalOrS3($request->file, Policy::FILE_PATH);
        }

        if ($request->saveAs == 'send') {
            $policy->status = 'published';
            $policy->publish_date = Carbon::now()->format('Y-m-d');
            $policy->save();

            if ($request->has('send_email') && $request->send_email == 'on') {
                $this->usersToNotify($policy);
            }
        } else {
            $policy->save();
        }

        DB::commit();

        return Reply::successWithData(__('messages.recordSaved'), ['policyId' => $policy->id, 'redirectUrl' => route('policy.index')]);
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $policy = Policy::where('id', $id)->withTrashed();

        $this->policy = $policy->with(['employeeAcknowledge' => function ($q) {}, 'addedBy', 'updatedBy'])->first();

        $this->isAcknowledged = $policy->with(['employeeAcknowledge' => function ($q) {
            $q->where('user_id', user()->id);
        }])->withTrashed()->first();

        $this->ackPermission = $policy->where(function ($query) {
            $query->where(function ($q) {
                $q->orWhere('department_id_json', 'like', '%"' . user()->employeeDetails->department_id . '"%')
                    ->orWhereNull('department_id_json');
            });
            $query->where(function ($q) {
                $q->orWhere('designation_id_json', 'like', '%"' . user()->employeeDetails->designation_id . '"%')
                    ->orWhereNull('designation_id_json');
            });
            $query->where(function ($q) {
                $q->orWhere('employment_type_json', 'like', '%"' . user()->employeeDetails->employment_type . '"%')
                    ->orWhereNull('employment_type_json');
            });
            $query->where(function ($q) {
                $q->orWhere('gender', user()->gender)
                    ->orWhereNull('gender');
            });
            $query->where('status', 'published');
        })->first();

        $this->viewPermission = user()->permission('view_policy');

        $this->department = !is_null($this->policy->department_id_json) ? in_array(user()->employeeDetails->department_id, json_decode($this->policy->department_id_json)) : true;
        $this->designation = !is_null($this->policy->designation_id_json) ? in_array(user()->employeeDetails->designation_id, json_decode($this->policy->designation_id_json)) : true;
        $this->employmentType = !is_null($this->policy->employment_type_json) ? in_array(user()->employeeDetails->employment_type, json_decode($this->policy->employment_type_json)) : true;

        abort_403(!(
            $this->viewPermission == 'all'
            || ($this->viewPermission == 'added' && $this->policy->added_by == user()->id)
            || ($this->viewPermission == 'owned' && $this->department && $this->designation && $this->employmentType)
            || ($this->viewPermission == 'both' && ($this->policy->added_by == user()->id || $this->department && $this->designation && $this->employmentType))
        ));

        $userIds = PolicyEmployeeAcknowledged::where('policy_id', $id)->pluck('user_id')->toArray();
        $this->nonAcknowledgeCount = EmployeeDetails::with('user')->whereHas('user', function ($q) use ($userIds) {
            $q->whereNotIn('id', $userIds)->where('status', 'active');
        })->count();

        $tab = request('tab');

        switch ($tab) {
            case 'acknowledged':
                return $this->acknowledgeDatatable($id);
                break;
            case 'non-acknowledged':
                return $this->nonAcknowledgeDatatable($id);
                break;
            default:
                $this->view = 'policy::policy.ajax.policy';

                $this->departments = !is_null($this->policy->department_id_json) ? implode(', ', $this->policy->department(json_decode($this->policy->department_id_json))) : '--';
                $this->designations = !is_null($this->policy->designation_id_json) ? implode(', ', $this->policy->designation(json_decode($this->policy->designation_id_json))) : '--';
                $this->employmentTypes = !is_null($this->policy->employment_type_json) ? collect(json_decode($this->policy->employment_type_json))
                    ->map(function ($employmentType) {
                        return __('modules.employees.' . $employmentType);
                    })->toArray() : '';
                $this->employmentTypes = $this->employmentTypes ? implode(', ', $this->employmentTypes) : '--';
                break;
        }

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        $this->activeTab = $tab ?: 'policy';

        return view('policy::policy.show', $this->data);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $this->policy = Policy::findOrFail($id);

        $this->editPermission = user()->permission('edit_policy');

        $this->department = !is_null($this->policy->department_id_json) ? in_array(user()->employeeDetails->department_id, json_decode($this->policy->department_id_json)) : true;
        $this->designation = !is_null($this->policy->designation_id_json) ? in_array(user()->employeeDetails->designation_id, json_decode($this->policy->designation_id_json)) : true;
        $this->employmentType = !is_null($this->policy->employment_type_json) ? in_array(user()->employeeDetails->employment_type, json_decode($this->policy->employment_type_json)) : true;

        abort_403(!(
            $this->editPermission == 'all'
            || ($this->editPermission == 'added' && $this->policy->added_by == user()->id)
            || ($this->editPermission == 'owned' && $this->department && $this->designation && $this->employmentType)
            || ($this->editPermission == 'both' && ($this->policy->added_by == user()->id || $this->department && $this->designation && $this->employmentType))
        ));

        $this->pageTitle = __('policy::app.editPolicy');
        $this->view = 'policy::policy.ajax.edit';

        $this->teams = Team::all();
        $this->designations = Designation::allDesignations();

        $this->departmentId = $this->policy->department_id_json;
        $this->departmentArray = $this->departmentId ? json_decode($this->departmentId, true) : [];

        if (!is_array($this->departmentArray)) {
            $this->departmentArray = [];
        }

        $this->designationId = $this->policy->designation_id_json;
        $this->designationArray = $this->designationId ? json_decode($this->designationId, true) : [];

        if (!is_array($this->designationArray)) {
            $this->designationArray = [];
        }

        $this->employmentType = $this->policy->employment_type_json;
        $this->employmentTypeArray = $this->employmentType ? json_decode($this->employmentType, true) : [];

        if (!is_array($this->employmentTypeArray)) {
            $this->employmentTypeArray = [];
        }

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('policy::policy.create', $this->data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePolicy $request, $id)
    {
        DB::beginTransaction();

        $policy = Policy::findOrFail($id);
        $date = (!is_null($request->date)) ? Carbon::createFromFormat($this->company->date_format, $request->date)->format('Y-m-d') : null;
        $policy->title = $request->title;
        $policy->company_id = company()->id;
        $policy->date  = $date;

        if ($policy->status == 'draft') {
            if ($request->type == 'file' && request()->hasFile('file')) {
                Files::deleteFile($policy->filename, Policy::FILE_PATH);
                $policy->filename = Files::uploadLocalOrS3($request->file, Policy::FILE_PATH);
                $policy->description = null;
            } else {
                $policy->description = ($request->type == 'description') ? $request->description : null;
            }
        }

        $policy->signature_required = $request->signature_required == 'on' ? 'yes' : 'no';
        $policy->department_id_json = $request->department ? json_encode($request->department) : null;
        $policy->designation_id_json = $request->designation ? json_encode($request->designation) : null;
        $policy->employment_type_json = $request->employment_type ? json_encode($request->employment_type) : null;
        $policy->updated_by = user()->id;
        $policy->gender  = !is_null($request->gender) ? $request->gender : null;

        if ($request->saveAs == 'send') {
            $policy->status = 'published';
            $policy->publish_date = Carbon::now()->format('Y-m-d');
            $policy->save();

            $this->usersToNotify($policy);
        } else {
            $policy->save();
        }

        DB::commit();

        return Reply::successWithData(__('messages.updateSuccess'), ['redirectUrl' => route('policy.index')]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $this->policy = Policy::withTrashed()->findOrFail($id);
        $this->deletePermission = user()->permission('delete_policy');

        abort_403(!(
            $this->deletePermission == 'all'
            || ($this->deletePermission == 'added' && $this->policy->added_by == user()->id)
            || ($this->deletePermission == 'owned' && $this->department && $this->designation && $this->employmentType)
            || ($this->deletePermission == 'both' && ($this->policy->added_by == user()->id || $this->department && $this->designation && $this->employmentType))
        ));

        $this->department = !is_null($this->policy->department_id_json) ? in_array(user()->employeeDetails->department_id, json_decode($this->policy->department_id_json)) : true;
        $this->designation = !is_null($this->policy->designation_id_json) ? in_array(user()->employeeDetails->designation_id, json_decode($this->policy->designation_id_json)) : true;
        $this->employmentType = !is_null($this->policy->employment_type_json) ? in_array(user()->employeeDetails->employment_type, json_decode($this->policy->employment_type_json)) : true;

        if (!is_null($this->policy->file)) {
            Files::deleteFile($this->policy->filename, Policy::FILE_PATH);
        }

        $this->policy->forceDelete();

        return Reply::successWithData(__('messages.deleteSuccess'), ['redirectUrl' => route('policy.index')]);
    }

    public function policySign($id)
    {
        $this->policy = Policy::find($id);

        return view('policy::policy.ajax.sign', $this->data);
    }

    public function policySignStore($id, StoreSignature $request)
    {
        DB::beginTransaction();

        $acknowledge = new PolicyEmployeeAcknowledged();
        $imageName = null;

        Files::createDirectoryIfNotExist('policy/sign');

        if ($request->signature_type == 'signature') {
            $image = $request->signature;  // your base64 encoded
            $image = str_replace('data:image/png;base64,', '', $image);
            $image = str_replace(' ', '+', $image);
            $imageName = str_random(32) . '.' . 'jpg';

            File::put(public_path() . '/' . Files::UPLOAD_FOLDER . '/policy/sign/' . $imageName, base64_decode($image));
            Files::uploadLocalFile($imageName, 'policy/sign', $acknowledge->company_id);
        } else {
            if ($request->hasFile('image')) {
                $imageName = Files::uploadLocalOrS3($request->image, 'policy/sign', 300);
            }
        }

        $acknowledge->company_id = company()->id;
        $acknowledge->signature_file = $imageName;
        $acknowledge->user_id = user()->id;
        $acknowledge->acknowledged_on = now();
        $acknowledge->policy_id = $id;
        $acknowledge->ip = $request->ip();
        $acknowledge->save();

        $this->saveAcknowledgeAsPdf($id, user()->id);

        $this->acknowledgedByEmail($id, user()->id);

        DB::commit();

        return Reply::successWithData(__('messages.signatureAdded'), ['status' => 'success']);
    }

    public function acknowledgedByEmail($id, $userId)
    {
        $acknowledgeBy = User::findOrFail($userId);
        $policy = Policy::with('addedBy')->findOrFail($id);

        $allUsers = User::all();
        $userWithPer = [];

        foreach ($allUsers as $user) {
            if ($user->permission('add_policy') == 'all') {
                $userWithPer[] = $user->id;
            }
        }

        $admins = User::allAdmins()->pluck('id')->toArray();
        $mergedUsers = array_unique(array_merge($userWithPer, $admins));

        $users = User::whereIn('id', $mergedUsers)->get();
        event(new PolicyAcknowledgedEvent($policy, $acknowledgeBy, $users));
    }

    public function saveAcknowledgeAsPdf($id, $userId)
    {
        $this->policy = Policy::with(['employeeAcknowledge' => function ($query) use ($userId) {
            $query->where('user_id', $userId);
        }])->findOrFail($id);

        App::setLocale(company()->locale ?? 'en');
        Carbon::setLocale(company()->locale ?? 'en');

        Files::createDirectoryIfNotExist('policy/sign');

        $pdfOption = $this->domPdfObjectForAcknowledge($id, $userId);
        $pdf = $pdfOption['pdf'];
        $filename = $pdfOption['fileName'];
        Files::deleteFile($filename . '.pdf', 'policy/sign');
        $pdf->download($filename . '.pdf');
        $pdf->save(public_path('/user-uploads/policy/sign/') . $filename . '.pdf');
    }

    public function domPdfObjectForAcknowledge($id, $userId)
    {
        $this->policy = Policy::with(['employeeAcknowledge' => function ($query) use ($userId) {
            $query->where('user_id', $userId);
        }])->findOrFail($id);
        App::setLocale(company()->locale ?? 'en');
        Carbon::setLocale(company()->locale ?? 'en');

        $pdf = app('dompdf.wrapper');
        $pdf->setOption('enable_php', true);
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', true);

        $pdf->loadView('policy::policy.pdf.policy-sign', $this->data);

        $filename = $this->policy->title . '-' . $id . '-' . $userId;

        return [
            'pdf' => $pdf,
            'fileName' => $filename
        ];
    }

    public function policyAcknowledge($id)
    {
        DB::beginTransaction();

        $acknowledge = new PolicyEmployeeAcknowledged();
        $acknowledge->company_id = company()->id;
        $acknowledge->user_id = user()->id;
        $acknowledge->acknowledged_on = now();
        $acknowledge->policy_id = $id;
        $acknowledge->ip = request()->ip();

        $acknowledge->save();

        $this->saveAcknowledgeAsPdf($id, user()->id);
        $this->acknowledgedByEmail($id, user()->id);

        DB::commit();

        return Reply::success(__('messages.recordSaved'));
    }

    public function acknowledgeDatatable($id)
    {
        $viewPermission = user()->permission('view_acknowledged');

        abort_403(!in_array($viewPermission, ['all', 'owned']));

        $dataTable = new AcknowledgedDataTable($id);

        $tab = request('tab');
        $this->activeTab = $tab ?: 'policy';
        $this->view = 'policy::policy.ajax.acknowledged';

        return $dataTable->render('policy::policy.show', $this->data);
    }

    public function nonAcknowledgeDatatable($id)
    {
        $viewPermission = user()->permission('view_non_acknowledged');

        abort_403($viewPermission != 'all');

        $dataTable = new NonAcknowledgedDataTable($id);

        $tab = request('tab');
        $this->activeTab = $tab ?: 'policy';
        $this->view = 'policy::policy.ajax.non_acknowledged';

        return $dataTable->render('policy::policy.show', $this->data);
    }

    public function download($id, $userId)
    {
        $this->policy = Policy::withTrashed()->with(['employeeAcknowledge' => function ($query) use ($userId) {
            $query->where('user_id', $userId);
        }])->findOrFail($id);

        App::setLocale(company()->locale ?? 'en');
        Carbon::setLocale(company()->locale ?? 'en');

        if (!is_null($this->policy->filename)) {
            $fileSystem = new Filesystem();
            $oMerger = new PDFMerger($fileSystem);
            $oMerger->init();

            // Add folder if doesn't exist
            $path = base_path(public_path() . '/' . Files::UPLOAD_FOLDER . '/policy/file');

            if (!File::isDirectory($path)) {
                Files::createDirectoryIfNotExist('policy/file');
            }

            $pdfName = str_random(32) . '.' . 'pdf';
            $filePath = Policy::FILE_PATH . '/' . $this->policy->filename;

            if (in_array(config('filesystems.default'), StorageSetting::S3_COMPATIBLE_STORAGE) && Storage::disk(config('filesystems.default'))->exists($filePath)) {

                $fileContent = Storage::disk(config('filesystems.default'))->get($filePath);
                File::put(public_path() . '/' . Files::UPLOAD_FOLDER . '/policy/file/' . $pdfName, $fileContent);
                $oMerger->addPDF(public_path('user-uploads/policy/file/') . $pdfName, 'all');
            } else {
                $oMerger->addPDF(public_path('user-uploads/policy/file/') . $this->policy->filename, 'all');
            }

            $oMerger->addPDF(public_path('user-uploads/policy/sign/') . $this->policy->title . '-' . $id . '-' . $userId . '.pdf', 'all');
            $oMerger->merge();

            $fileName = 'acknowledged_file.pdf';

            if (in_array(config('filesystems.default'), StorageSetting::S3_COMPATIBLE_STORAGE) && Storage::disk(config('filesystems.default'))->exists($filePath)) {

                $mergedPdfContent = $oMerger->output();

                Files::deleteFile($fileName, Policy::FILE_PATH);
                $oMerger->save(public_path('/user-uploads/policy/file/') . $fileName);

                $localFilePath = public_path('/user-uploads/policy/file/') . $fileName;

                if (!File::exists($localFilePath)) {
                    return redirect()->back();
                }

                $tempFilePath = tempnam(sys_get_temp_dir(), $fileName);
                file_put_contents($tempFilePath, $mergedPdfContent);

                $uploadedFile = new \Illuminate\Http\UploadedFile(
                    $tempFilePath,
                    $fileName,
                    'application/pdf',
                    null,
                    true
                );

                $this->policy->name = Files::uploadLocalOrS3($uploadedFile, Policy::FILE_PATH);
                $this->policy->save();

                $mainFilePath = public_path('/user-uploads/policy/file/') . $pdfName;

                File::delete($mainFilePath);
                File::delete($localFilePath);
                File::delete($tempFilePath);

                $policy = $this->policy;
                $policy->filename = $policy->name;

                return download_local_s3($this->policy, Policy::FILE_PATH . '/' . $policy->name);
            } else {
                $oMerger->save(public_path('/user-uploads/policy/file/') . $fileName . '.pdf');
            }
        }

        $pdfOption = $this->domPdfObjectForDownload($id, $userId);
        $pdf = $pdfOption['pdf'];
        $filename = $pdfOption['fileName'];

        return request()->view ? $pdf->stream($filename . '.pdf') : $pdf->download($filename . '.pdf');
    }

    public function domPdfObjectForDownload($id, $userId)
    {
        $this->policy = Policy::withTrashed()->with(['employeeAcknowledge' => function ($query) use ($userId) {
            $query->where('user_id', $userId);
        }])->findOrFail($id);
        App::setLocale(company()->locale ?? 'en');
        Carbon::setLocale(company()->locale ?? 'en');
        $pdf = app('dompdf.wrapper');
        $pdf->setOption('enable_php', true);
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', true);

        $pdf->loadView('policy::policy.pdf.policy', $this->data);

        $filename = $this->policy->title;

        return [
            'pdf' => $pdf,
            'fileName' => $filename
        ];
    }

    /**
     * XXXXXXXXXXX
     *
     * @return \Illuminate\Http\Response
     */
    public function archive(ArchivePolicyDataTable $dataTable)
    {
        $canManagePermission = user()->permission('can_archive_policy');
        abort_403(!in_array($canManagePermission, ['all']));

        $this->pageTitle = 'policy::app.policyCenter';
        $this->teams = Team::all();
        $this->designations = Designation::allDesignations();

        return $dataTable->render('policy::policy.archive', $this->data);
    }

    public function archiveRestore($id)
    {
        $canManagePermission = user()->permission('can_archive_policy');
        abort_403(!in_array($canManagePermission, ['all']));

        $policy = Policy::withTrashed()->findOrFail($id);
        $policy->restore();

        return Reply::success(__('policy::messages.policyRevertSuccessfully'));
    }

    public function archiveDestroy($id)
    {
        Policy::destroy($id);

        $notifyData = 'Modules\Policy\Notifications\PolicyPublishedNotification';

        Notification::where('type', $notifyData)
            ->whereNull('read_at')
            ->where('data', 'like', '%"policy_id":' . $id . '%')
            ->delete();

        return Reply::successWithData(__('policy::messages.archiveSuccess'), ['redirectUrl' => route('policy.index')]);
    }

    public function publishPolicy($id)
    {
        $policy = Policy::findOrFail($id);
        $policy->status = 'published';
        $policy->publish_date = Carbon::now()->format('Y-m-d');
        $policy->save();

        $this->usersToNotify($policy);

        return Reply::success(__('policy::messages.publishSuccess'));
    }

    public function usersToNotify($policy)
    {
        $department = $policy->department_id_json ? json_decode($policy->department_id_json) : [];
        $designation = $policy->designation_id_json ? json_decode($policy->designation_id_json) : [];
        $employmentType = $policy->employment_type_json ? json_decode($policy->employment_type_json) : [];

        $users = User::whereHas('employee', function ($q) use ($department, $designation, $employmentType) {
            if (!empty($department)) {
                $q->whereIn('employee_details.department_id', $department);
            }

            if (!empty($designation)) {
                $q->whereIn('employee_details.designation_id', $designation);
            }

            if (!empty($employmentType)) {
                $q->whereIn('employee_details.employment_type', $employmentType);
            }
        });

        if (!empty($policy->gender)) {
            $users = $users->where('users.gender', $policy->gender);
        }

        $users = $users->where('status', 'active')->get();

        event(new PolicyPublishedEvent($policy, $users));
    }

    public function sendRemainder($id)
    {
        $policy = Policy::findOrFail($id);

        $userIds = PolicyEmployeeAcknowledged::where('policy_id', $id)->pluck('user_id')->toArray();
        $users = User::whereNotIn('id', $userIds)->where('status', 'active');

        $department = $policy->department_id_json ? json_decode($policy->department_id_json) : [];
        $designation = $policy->designation_id_json ? json_decode($policy->designation_id_json) : [];
        $employmentType = $policy->employment_type_json ? json_decode($policy->employment_type_json) : [];

        $users->whereHas('employee', function ($q) use ($department, $designation, $employmentType) {
            if (!empty($department)) {
                $q->whereIn('employee_details.department_id', $department);
            }
            if (!empty($designation)) {
                $q->whereIn('employee_details.designation_id', $designation);
            }
            if (!empty($employmentType)) {
                $q->whereIn('employee_details.employment_type', $employmentType);
            }
        });

        if (!empty($policy->gender)) {
            $users = $users->where('users.gender', $policy->gender);
        }

        $users = $users->get();
        event(new SendReminderEvent($policy, $users));

        return Reply::success(__('policy::messages.reminderSuccess'));
    }
}
