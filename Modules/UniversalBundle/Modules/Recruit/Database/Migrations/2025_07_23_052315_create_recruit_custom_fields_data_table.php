<?php

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
        Schema::create('recruit_custom_fields_data', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('custom_field_id')->index('recruit_custom_fields_data_custom_field_id_foreign');
            $table->unsignedInteger('category_id');
            $table->foreign(['custom_field_id'])->references(['id'])->on('recruit_custom_fields')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->string('category')->nullable()->index();
            $table->string('value', 10000);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recruit_custom_fields_data');
    }
};
