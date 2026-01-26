<?php

namespace Modules\Recruit\Database\Seeders;

use App\Models\User;
use Carbon\Carbon;
use Faker\Factory;
use Illuminate\Database\Seeder;
use Modules\Recruit\Entities\ApplicationSource;
use Modules\Recruit\Entities\RecruitApplicationStatus;
use Modules\Recruit\Entities\RecruitJob;
use Modules\Recruit\Entities\RecruitJobApplication;

class JobApplicationsTableSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */

    public function run($companyId)
    {
        $faker = Factory::create();

        $source = ApplicationSource::pluck('id');
        $job_id = RecruitJob::where('company_id', $companyId)->pluck('id');
        $status = RecruitApplicationStatus::where('company_id', $companyId)->pluck('id');
        $user = User::where('company_id', $companyId)->pluck('id');

        for ($i = 0; $i < 100; $i++) {
            $jobApp = [
                'full_name' => $faker->name,
                'email' => $faker->unique()->safeEmail(),
                'phone' => $faker->numerify('9#########'),
                'date_of_birth' => Carbon::today()->subYears(rand(18, 35))->subDays(rand(0, 365)),
                'gender' => $faker->randomElement(['male', 'female', 'other']),
                'application_source_id' => $source->random(),
                'recruit_job_id' => $job_id->random(),
                'recruit_application_status_id' => $status->random(),
                'location_id' => 1,
                'total_experience' => $faker->randomElement(['0-1', '1-2', '3-4', '5-7']),
                'current_location' => $faker->city,
                'current_ctc' => $faker->numberBetween(300, 9000),
                'expected_ctc' => $faker->numberBetween(700, 12000),
                'currenct_ctc_rate' => $faker->randomElement(['Year', 'Month', 'Day']),
                'expected_ctc_rate' => $faker->randomElement(['Year', 'Month', 'Day']),
                'notice_period' => (string)$faker->randomElement([15, 30, 45, 60]),
                'application_sources' => $faker->randomElement(['addedByUser', 'linkedIn', 'referral']),
                'added_by' => $user->random(),
                'company_id' => $companyId,
            ];

            if ($faker->boolean(10)) {
                $jobApp['deleted_at'] = Carbon::today()->subDays(rand(10, 90));
            }

            RecruitJobApplication::create($jobApp);
        }
    }

}
