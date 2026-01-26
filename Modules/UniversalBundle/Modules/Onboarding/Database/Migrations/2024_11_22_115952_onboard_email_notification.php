<?php

use App\Models\Company;
use App\Models\EmployeeDetails;
use Illuminate\Database\Migrations\Migration;
use Modules\Onboarding\Entities\OnboardingCompletedTask;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Onboarding\Entities\OnboardingNotificationSetting;

return new class extends Migration
{
    /**
     * Run the migrations.
     */

    public function up(): void
    {

        Schema::create('onboarding_notification_settings', function (Blueprint $table) {
            $table->id();
            $table->integer('company_id')->unsigned();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->string('setting_name');
            $table->enum('send_email', ['yes', 'no'])->default('yes');
            $table->string('slug')->nullable();
            $table->timestamps();
        });

        $companies = Company::all();

        foreach ($companies as $company) {

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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }

};
