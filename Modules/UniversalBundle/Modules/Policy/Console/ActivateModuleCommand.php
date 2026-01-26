<?php

namespace Modules\Policy\Console;

use App\Models\Company;
use Illuminate\Console\Command;
use Modules\Asset\Entities\AssetSetting;
use Modules\Policy\Entities\PolicySetting;

class ActivateModuleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'policy:activate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add all the module settings of asset module';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $companies = Company::all();

        foreach ($companies as $company) {
            PolicySetting::addModuleSetting($company);
        }
    }

}
