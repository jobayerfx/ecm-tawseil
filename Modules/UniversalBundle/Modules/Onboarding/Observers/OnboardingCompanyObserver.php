<?php

namespace Modules\Onboarding\Observers;

use Modules\Onboarding\Entities\OnboardingTask;

class OnboardingCompanyObserver
{
    public function creating(OnboardingTask $model)
    {
        if (company()) {
            $model->company_id = company()->id;
        }

        $model->added_by = auth()->id();

    }
}
