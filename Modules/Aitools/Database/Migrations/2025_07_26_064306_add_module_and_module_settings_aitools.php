<?php

use App\Models\Company;
use App\Models\Module;
use Illuminate\Database\Migrations\Migration;
use Modules\Aitools\Entities\AiToolsSetting;

return new class extends Migration
{

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Module::firstOrCreate(['module_name' => AiToolsSetting::MODULE_NAME]);

        Company::chunk(50, function ($companies) {
            foreach ($companies as $company) {
                AiToolsSetting::addModuleSetting($company);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }

};
