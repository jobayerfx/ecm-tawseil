<?php

namespace Modules\ServerManager\Database\Seeders;

use Modules\ServerManager\Entities\ServerHosting;
use Modules\ServerManager\Entities\ServerProvider;
use Modules\ServerManager\Entities\ServerType;
use App\Models\User;
use App\Models\Project;
use App\Models\ClientDetails;
use Illuminate\Database\Seeder;
use Modules\ServerManager\Database\Seeders\ServerProviderSeeder;
use Modules\ServerManager\Database\Seeders\ServerTypeSeeder;

class ServerHostingSeeder extends Seeder
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

        // Ensure providers and server types are seeded first
        $providerSeeder = new ServerProviderSeeder();
        $providerSeeder->seedProvidersForCompany($companyId);

        $serverTypeSeeder = new ServerTypeSeeder();
        $serverTypeSeeder->seedServerTypesForCompany($companyId);

        // Get available users, projects, and clients for this company
        $users = User::where('company_id', $companyId)->pluck('id')->toArray();
        $projects = Project::where('company_id', $companyId)->pluck('id')->toArray();
        $clients = ClientDetails::where('company_id', $companyId)->pluck('id')->toArray();

        // Get providers and server types from database
        $providers = ServerProvider::where('company_id', $companyId)
            ->where(function($query) {
                $query->where('type', 'hosting')
                      ->orWhere('type', 'both');
            })
            ->where('status', 'active')
            ->get(['id', 'name', 'url'])
            ->values();

        $serverTypes = ServerType::where('company_id', $companyId)
            ->where('status', 'active')
            ->pluck('id')
            ->toArray();

        // Only create server hosting records if users exist (required for foreign key constraints)
        if (!empty($users)) {
            ServerHosting::factory()
                ->count(20)
                ->make()
                ->each(function (ServerHosting $hosting) use ($companyId, $faker, $users, $projects, $clients, $providers, $serverTypes) {
                    $hosting->company_id = $companyId;

                    // Assign random user (users are guaranteed to exist at this point)
                    $hosting->assigned_to = $faker->randomElement($users);
                    $hosting->created_by = $faker->randomElement($users);
                    $hosting->updated_by = $faker->randomElement($users);

                    // Assign random provider from database (hosting_provider and provider_url)
                    $selectedProvider = $providers->random();
                    $hosting->hosting_provider = $selectedProvider->id;
                    $hosting->provider_url = $selectedProvider->url;

                    // Assign random server type from database if available
                    if (!empty($serverTypes)) {
                        $hosting->server_type = $faker->randomElement($serverTypes);
                    }

                    // Assign random project if available
                    if (!empty($projects)) {
                        $hosting->project = $faker->optional(0.7)->randomElement($projects);
                    }

                    // Assign random client if available
                    if (!empty($clients)) {
                        $hosting->client = $faker->optional(0.6)->randomElement($clients);
                    }

                    $hosting->ssl_certificate = $faker->boolean();
                    $hosting->expiry_notification = $faker->boolean();
                    $hosting->notification_days_before = $faker->numberBetween(1, 30);

                    $hosting->save();
                });
        }
    }
}
