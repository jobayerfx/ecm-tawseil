<?php

namespace Modules\ServerManager\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\ServerManager\Entities\ServerHosting;
use Modules\ServerManager\Notifications\HostingExpiringNotification;
use App\Models\User;
use App\Models\Permission;
use App\Models\PermissionType;
use App\Models\UserPermission;

class CheckExpiringHostingsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 120;
    protected $companyId;

    /**
     * Create a new job instance.
     */
    public function __construct($companyId = null)
    {
        $this->companyId = $companyId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $companyId = $this->companyId ?? company()->id;

        $hostings = ServerHosting::where('company_id', $companyId)
            ->where('status', 'active')
            ->where('expiry_notification', true)
            ->whereNotNull('notification_days_before')
            ->whereNotNull('renewal_date')
            ->with(['assignedTo', 'createdBy'])
            ->get();

        foreach ($hostings as $hosting) {
            if ($hosting->shouldSendNotification()) {

                // Get users to notify
                $adminUsers = User::allAdmins($hosting->company_id)->pluck('id')->toArray();
                $usersToNotify = User::whereIn('id', $adminUsers)->get();

                // Send notifications to all users
                foreach ($usersToNotify as $user) {
                    $user->notify(new HostingExpiringNotification($hosting));
                }

                // Mark notification as sent
                // $hosting->markNotificationSent();
            }
        }
    }
}
