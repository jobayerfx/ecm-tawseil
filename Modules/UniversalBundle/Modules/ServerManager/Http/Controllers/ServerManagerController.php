<?php

namespace Modules\ServerManager\Http\Controllers;

use App\Http\Controllers\AccountBaseController;
// use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\ServerManager\Entities\ServerHosting;
use Modules\ServerManager\Entities\ServerDomain;
use Modules\ServerManager\Entities\ServerLog;
use Modules\ServerManager\Entities\ServerSetting;
use App\Helper\Reply;
use Illuminate\Support\Facades\Request;

class ServerManagerController extends AccountBaseController
{
    public $pageTitle;

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = __('servermanager::app.menu.serverManager');
        $this->middleware(function ($request, $next) {
            abort_403(!in_array(ServerSetting::MODULE_NAME, $this->user->modules));

            return $next($request);
        });
    }


    /**
     * Display the server manager dashboard.
     */
    public function index()
    {

        $this->pageTitle = __('servermanager::app.menu.serverManager');
        $this->loggedEmployee = user();

        $viewHostingPermission = user()->permission('view_hosting');
        $viewDomainPermission = user()->permission('view_domain');
        if ($viewHostingPermission == 'none' && $viewDomainPermission == 'none') {
            abort_403('You are not authorized to access this page');
        }

        // Hostings
        $hostingBase = ServerHosting::where('company_id', company()->id);
        if ($viewHostingPermission == 'all') {
            $this->totalHostings = $hostingBase->count();
            $this->activeHostings = $hostingBase->where('status', 'active')->count();
            $this->expiringHostings = $hostingBase->where('status', 'active')
                ->whereBetween('renewal_date', [now(), now()->addDays(30)])->count();
            $this->expiringHostingsList = $hostingBase->where('status', 'active')
                ->whereBetween('renewal_date', [now(), now()->addDays(30)])
                ->orderBy('renewal_date', 'asc')->limit(5)->get();
        } else {
            $hostingUser = $hostingBase->where('created_by', user()->id);
            $this->totalHostings = $hostingUser->count();
            $this->activeHostings = $hostingUser->where('status', 'active')->count();
            $this->expiringHostings = $hostingUser->where('status', 'active')
                ->whereBetween('renewal_date', [now(), now()->addDays(30)])->count();
            $this->expiringHostingsList = $hostingUser->where('status', 'active')
                ->whereBetween('renewal_date', [now(), now()->addDays(30)])
                ->orderBy('renewal_date', 'asc')->limit(5)->get();
        }

        // Domains
        $domainBase = ServerDomain::where('company_id', company()->id);
        if ($viewDomainPermission == 'all') {
            $this->totalDomains = $domainBase->count();
            $this->activeDomains = $domainBase->where('status', 'active')->count();
            $this->expiringDomains = $domainBase->where('status', 'active')
                ->whereBetween('expiry_date', [now(), now()->addDays(30)])->count();
            $this->expiringDomainsList = $domainBase->where('status', 'active')
                ->whereBetween('expiry_date', [now(), now()->addDays(30)])
                ->orderBy('expiry_date', 'asc')->limit(5)->get();
        } else {
            $domainUser = $domainBase->where('created_by', user()->id);
            $this->totalDomains = $domainUser->count();
            $this->activeDomains = $domainUser->where('status', 'active')->count();
            $this->expiringDomains = $domainUser->where('status', 'active')
                ->whereBetween('expiry_date', [now(), now()->addDays(30)])->count();
            $this->expiringDomainsList = $domainUser->where('status', 'active')
                ->whereBetween('expiry_date', [now(), now()->addDays(30)])
                ->orderBy('expiry_date', 'asc')->limit(5)->get();
        }

        // Get recent activities
        $this->recentLogs = ServerLog::where('company_id', company()->id)
            ->with(['performedBy', 'entity'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Ensure pageTitle is included in the data array
        $this->data['pageTitle'] = $this->pageTitle;

        return view('servermanager::dashboard.index', $this->data);
    }

    /**
     * Get server manager statistics for AJAX requests.
     */
    public function getStatistics()
    {
        $viewPermission = user()->permission('view_server_statistics');
        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both']));
        $companyId = company()->id;

        $statistics = [
            'hostings' => [
                'total' => ServerHosting::where('company_id', $companyId)->count(),
                'active' => ServerHosting::where('company_id', $companyId)
                    ->where('status', 'active')
                    ->count(),
                'expiring' => ServerHosting::where('company_id', $companyId)
                    ->where('renewal_date', '<=', now()->addDays(30))
                    ->count(),
            ],
            'domains' => [
                'total' => ServerDomain::where('company_id', $companyId)->count(),
                'active' => ServerDomain::where('company_id', $companyId)
                    ->where('status', 'active')
                    ->count(),
                'expiring' => ServerDomain::where('company_id', $companyId)
                    ->where('expiry_date', '<=', now()->addDays(30))
                    ->count(),
            ],
        ];

        return Reply::dataOnly($statistics);
    }

    /**
     * Get recent activities for AJAX requests.
     */
    public function getRecentActivities()
    {
        $viewPermission = user()->permission('view_server_activities');
        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both']));
        $companyId = company()->id;

        $activities = ServerLog::where('company_id', $companyId)
            ->with(['performedBy'])
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'action' => $log->action,
                    'entity_type' => $log->entity_type,
                    'description' => $log->description,
                    'performed_by' => $log->performedBy->name ?? 'System',
                    'created_at' => $log->created_at->format('M d, Y H:i'),
                    'badge_class' => $log->getActionBadgeClass(),
                ];
            });

        return Reply::dataOnly($activities);
    }


}