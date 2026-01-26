<?php

namespace Modules\Onboarding\Console;

use App\Models\Company;
use Illuminate\Console\Command;
use Modules\Onboarding\Entities\OnboardingSetting;
use Modules\Purchase\Entities\PurchaseSetting;

class ActivateModuleCommand extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'onboarding:activate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add all the module settings of Onboarding module';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $companies = Company::get();

        foreach ($companies as $company) {
            OnboardingSetting::addModuleSetting($company);
        }
    }

}
