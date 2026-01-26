<?php

namespace Modules\Onboarding\Entities;

use App\Models\BaseModel;
use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OnboardingNotificationSetting extends BaseModel
{
    use HasFactory;
    use HasCompany;

    public static function addNotificationSetting($company)
    {
        $notificationDetails = [
            ['company_id' => $company->id, 'setting_name' => 'Onboard Notification', 'send_email' => 'yes', 'slug' => 'onboard-notification'],
            ['company_id' => $company->id, 'setting_name' => 'Onboard Notification', 'send_email' => 'yes', 'slug' => 'offboard-notification']
        ];

        foreach($notificationDetails as $notification){
            $notify = OnboardingNotificationSetting::where('company_id', $notification['company_id'])->where('slug', $notification['slug'])->first();

            if(!$notify){
                OnboardingNotificationSetting::create($notification);
            }
        }
    }

}
