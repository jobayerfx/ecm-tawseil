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
        Schema::create('purchase_digital_signature_setting', function (Blueprint $table) {
            $table->id();
            $table->integer('company_id')->unsigned()->nullable();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');
            $table->string('signature')->nullable();
            $table->boolean('signature_in_vendor')->default(false);
            $table->boolean('signature_in_purchase_order')->default(false);
            $table->boolean('signature_in_bills')->default(false);
            $table->boolean('signature_in_vendor_payments')->default(false);
            $table->boolean('signature_in_vendor_credits')->default(false);
            $table->boolean('signature_in_inventory')->default(false);
            $table->string('added_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_digital_signature_setting');
    }
};
