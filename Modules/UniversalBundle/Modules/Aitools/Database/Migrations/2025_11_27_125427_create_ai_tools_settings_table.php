<?php

use App\Models\Company;
use Modules\Aitools\Entities\AiToolsSetting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ai_tools_settings', function (Blueprint $table) {
            $table->id();
            $table->integer('company_id')->unsigned()->nullable();
            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->text('chatgpt_api_key')->nullable();
            // model name
            $table->string('model_name')->nullable();
            $table->timestamps();
        });

        // Create default record for each existing company
        $companies = Company::select('id')->get();

        foreach ($companies as $company) {
            AiToolsSetting::create(['company_id' => $company->id]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_tools_settings');
    }
};
