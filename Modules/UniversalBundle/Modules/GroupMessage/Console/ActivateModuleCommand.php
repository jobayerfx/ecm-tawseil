<?php

namespace Modules\GroupMessage\Console;

use App\Models\Company;
use App\Models\Module;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Modules\GroupMessage\Entities\GroupMessageGlobalSetting;

class ActivateModuleCommand extends Command
{

    /**
     * The name and signature of the console command.
     */
    protected $signature = 'groupmessage:activate';

    /**
     * The console command description.
     */
    protected $description = 'Add all the module settings of group message module.';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Module::firstOrCreate(['module_name' => GroupMessageGlobalSetting::MODULE_NAME]);

        Company::select('id')->chunk(50, function ($companies) {
            foreach ($companies as $company) {
                GroupMessageGlobalSetting::addModuleSetting($company);
            }
        });
    }

    /**
     * Get the console command arguments.
     */
    protected function getArguments(): array
    {
        return [
            ['example', InputArgument::REQUIRED, 'An example argument.'],
        ];
    }

    /**
     * Get the console command options.
     */
    protected function getOptions(): array
    {
        return [
            ['example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null],
        ];
    }

}
