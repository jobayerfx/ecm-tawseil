<?php

namespace Modules\ServerManager\Listeners;

use App\Events\NewCompanyCreatedEvent;
use Modules\ServerManager\Entities\ServerSetting;
use Modules\ServerManager\Database\Seeders\ServerProviderSeeder;
use Modules\ServerManager\Database\Seeders\ServerTypeSeeder;
use Modules\ServerManager\Database\Seeders\ServerHostingSeeder;
use Modules\ServerManager\Database\Seeders\ServerDomainSeeder;

class CompanyCreatedListener
{
    /**
     * Handle the event.
     *
     * @param NewCompanyCreatedEvent $event
     * @return void
     */
    public function handle(NewCompanyCreatedEvent $event)
    {
        $company = $event->company;

        // Initialize ServerManager module settings for the new company
        ServerSetting::addModuleSetting($company);

        $this->seedInitialData($company);
    }

    /**
     * Seed initial data for the module
     */
    public static function seedInitialData($company)
    {
        // Seed server providers
        $providerSeeder = new ServerProviderSeeder();
        $providerSeeder->seedProvidersForCompany($company->id);

        // Seed server types
        $serverTypeSeeder = new ServerTypeSeeder();
        $serverTypeSeeder->seedServerTypesForCompany($company->id);

        if (! app()->environment('codecanyon')) {
            // Seed hosting data
            $hostingSeeder = new ServerHostingSeeder();
            $hostingSeeder->run($company->id);

            // Seed domain data
            $domainSeeder = new ServerDomainSeeder();
            $domainSeeder->run($company->id);
        }
    }
}
