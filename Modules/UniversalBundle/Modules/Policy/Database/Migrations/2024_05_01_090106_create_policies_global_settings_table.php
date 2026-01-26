<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Policy\Entities\PolicySetting;

return new class extends Migration
{

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        \App\Models\Module::validateVersion(PolicySetting::MODULE_NAME);

        if (!Schema::hasTable('policy_settings')) {
            Schema::create('policy_settings', function (Blueprint $table) {
                $table->id();
                $table->string('purchase_code')->nullable();
                $table->timestamp('supported_until')->nullable();
                $table->timestamp('purchased_on')->nullable();
                $table->boolean('notify_update')->default(1);
                $table->string('license_type', 20)->nullable();
                $table->timestamps();
            });

            PolicySetting::create([]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('policy_settings');
    }
};
