<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Module;

class AddServermanagerToModulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         // Add ServerManager module to modules table
         Module::firstOrCreate([
            'module_name' => 'servermanager',
        ], [
            'description' => 'Manage server hostings, domains, and related services',
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Module::where('module_name', 'servermanager')->delete();
    }
}
