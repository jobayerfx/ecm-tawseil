<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Company;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('recruit_custom_field_groups', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('company_id')->unsigned()->nullable();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');
            $table->string('name');
            $table->string('category')->nullable()->index();
        });

        $companies = Company::all();
        foreach ($companies as $company) {
            DB::table('recruit_custom_field_groups')->insert(
                [
                        'name' => 'Job', 'category' => 'Modules\Recruit\Entities\RecruitJob', 'company_id' => $company->id,
                ]
            );
        }

        Schema::create('recruit_custom_fields', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('company_id')->unsigned()->nullable();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');
            $table->unsignedInteger('custom_field_group_id')->nullable()->index('recruit_custom_fields_custom_field_group_id_foreign');
            $table->foreign(['custom_field_group_id'])->references(['id'])->on('recruit_custom_field_groups')->onUpdate('CASCADE')->onDelete('SET NULL');
            $table->string('label', 100);
            $table->string('name', 100);
            $table->string('type', 10);
            $table->enum('required', ['yes', 'no'])->default('no');
            $table->string('values', 5000)->nullable();
            $table->boolean('export')->nullable()->default(0);
            $table->enum('visible', ['true', 'false'])->default('false')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recruit_custom_field');
    }
};
