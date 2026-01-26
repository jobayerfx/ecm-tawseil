<?php

namespace Modules\GroupMessage\Listeners;

use Modules\GroupMessage\Entities\GroupMessageGlobalSetting;

class CompanyCreatedListener
{

    /**
     * Handle the event.
     */
    public function handle($event): void
    {
        $company = $event->company;
        GroupMessageGlobalSetting::addModuleSetting($company);
    }

}
