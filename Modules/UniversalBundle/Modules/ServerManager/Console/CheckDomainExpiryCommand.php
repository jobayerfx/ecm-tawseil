<?php

namespace Modules\ServerManager\Console;

use Illuminate\Console\Command;
use Modules\ServerManager\Jobs\CheckExpiringDomainsJob;
use App\Models\Company;

class CheckDomainExpiryCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'servermanager:check-domain-expiry';

    /**
     * The console command description.
     */
    protected $description = 'Check for expiring domains and send notifications';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Checking for expiring domains...');

        // Dispatch the job for each company
        $companies = Company::all();

        foreach ($companies as $company) {
            $this->info("Processing company: {$company->company_name}");

            // Dispatch the job with company ID
            CheckExpiringDomainsJob::dispatch($company->id);
        }

        $this->info('Domain expiry check completed successfully.');
        return Command::SUCCESS;
    }
}
