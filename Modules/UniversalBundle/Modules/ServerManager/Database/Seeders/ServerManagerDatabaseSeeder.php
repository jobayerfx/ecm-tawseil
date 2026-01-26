<?php

namespace Modules\ServerManager\Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;
use Modules\ServerManager\Database\Seeders\ServerHostingSeeder;
use Modules\ServerManager\Database\Seeders\ServerDomainSeeder;

class ServerManagerDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        config(['app.seeding' => true]);

        if (! app()->environment('codecanyon')) {
            $companies = Company::all();

            foreach ($companies as $company) {

                if (! App::environment('codecanyon')) {
                    $this->call(ServerHostingSeeder::class, false, ['companyId' => $company->id]);
                    $this->call(ServerDomainSeeder::class, false, ['companyId' => $company->id]);
                }

            }
        }

        config(['app.seeding' => false]);
    }
}
