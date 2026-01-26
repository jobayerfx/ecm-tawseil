<?php

namespace Modules\Aitools\Console;

use App\Models\Company;
use App\Models\Module;
use Illuminate\Console\Command;

class ActivateModuleCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'aitools:activate';

    /**
     * The console command description.
     */
    protected $description = 'Activate the Aitools module.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Module::firstOrCreate(['module_name' => 'aitools']);

        Company::select('id')->chunk(50, function ($companies) {
            foreach ($companies as $company) {
                \Modules\Aitools\Entities\AiToolsSetting::firstOrCreate(['company_id' => $company->id]);
            }
        });
        
        $this->info('Aitools module activated successfully.');

        $this->info('Aitools module activated successfully.');
    }
}

