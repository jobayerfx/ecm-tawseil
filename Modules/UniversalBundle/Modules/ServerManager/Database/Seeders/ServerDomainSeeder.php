<?php

namespace Modules\ServerManager\Database\Seeders;

use Modules\ServerManager\Entities\ServerDomain;
use Modules\ServerManager\Entities\ServerHosting;
use App\Models\User;
use App\Models\Project;
use App\Models\ClientDetails;
use Illuminate\Database\Seeder;
use Modules\ServerManager\Entities\ServerProvider;

class ServerDomainSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @param int $companyId
     * @return void
     */
    public function run($companyId)
    {
        $faker = \Faker\Factory::create();

        // Get available users, projects, and clients for this company
        $users = User::where('company_id', $companyId)->pluck('id')->toArray();
        $projects = Project::where('company_id', $companyId)->pluck('id')->toArray();
        $clients = ClientDetails::where('company_id', $companyId)->pluck('id')->toArray();

        $providers = ServerProvider::where('company_id', $companyId)
            ->where(function($query) {
                $query->where('type', 'hosting')
                      ->orWhere('type', 'both');
            })
            ->where('status', 'active')
            ->get(['id', 'name', 'url'])
            ->values();

        // Only create server domain records if users exist (required for foreign key constraints)
        if (!empty($users)) {
            ServerDomain::factory()
                ->count(20)
                ->make()
                ->each(function (ServerDomain $domain) use ($companyId, $faker, $users, $projects, $clients, $providers) {
                    $domain->company_id = $companyId;

                    // Assign random user (users are guaranteed to exist at this point)
                    // $domain->assigned_to = $faker->randomElement($users);
                    $domain->created_by = $faker->randomElement($users);
                    // $domain->updated_by = $faker->randomElement($users);

                    // Assign random project if available
                    if (!empty($projects)) {
                        $domain->project_id = $faker->optional(0.7)->randomElement($projects);
                    }

                    // Assign random client if available
                    if (!empty($clients)) {
                        $domain->client_id = $faker->optional(0.6)->randomElement($clients);
                    }

                     // Assign random provider from database (hosting_provider and provider_url)
                     $selectedProvider = $providers->random();
                     $domain->domain_provider = $selectedProvider->id;
                     $domain->provider_url = $selectedProvider->url;

                    // Assign random hosting if available
                    $hostings = ServerHosting::where('company_id', $companyId)->pluck('id')->toArray();
                    if (!empty($hostings)) {
                        $domain->hosting_id = $faker->optional(0.8)->randomElement($hostings);
                    }

                    $domain->save();
                });
        }
    }
}
