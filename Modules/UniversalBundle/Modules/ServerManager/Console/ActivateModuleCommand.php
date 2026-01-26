<?php

namespace Modules\ServerManager\Console;

use App\Models\Company;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Modules\ServerManager\Entities\ServerSetting;

class ActivateModuleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'servermanager:activate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Activate Server Manager Module';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Activating Server Manager Module...');

        // Run migrations
        $this->info('Running migrations...');
        Artisan::call('module:migrate', ['module' => 'ServerManager']);

        // Add module settings for all companies
        $this->info('Adding module settings...');
        $companies = Company::all();

        foreach ($companies as $company) {
            ServerSetting::addModuleSetting($company);
        }

        Artisan::call('cache:clear');

        $this->info('Server Manager Module activated successfully!');

        return Command::SUCCESS;
    }
}
