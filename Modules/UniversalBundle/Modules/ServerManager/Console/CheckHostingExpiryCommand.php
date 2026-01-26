<?php

namespace Modules\ServerManager\Console;

use Illuminate\Console\Command;
use Modules\ServerManager\Jobs\CheckExpiringHostingsJob;
use App\Models\Company;

class CheckHostingExpiryCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'servermanager:check-hosting-expiry';

    /**
     * The console command description.
     */
    protected $description = 'Check for expiring hostings and send notifications';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Checking for expiring hostings...');

        // Dispatch the job for each company
        $companies = Company::all();

        foreach ($companies as $company) {
            $this->info("Processing company: {$company->company_name}");

            // Dispatch the job with company ID
            CheckExpiringHostingsJob::dispatch($company->id);
        }

        $this->info('Hosting expiry check completed successfully.');
        return Command::SUCCESS;
    }
}
