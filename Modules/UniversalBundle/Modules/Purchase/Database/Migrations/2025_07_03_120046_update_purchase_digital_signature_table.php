<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Company;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $companies = Company::All();

        foreach ($companies as $company) {
            DB::table('purchase_digital_signature_setting')->insert([
                'company_id' => $company->id,
                'signature' => null,
                'signature_in_vendor' => false,
                'signature_in_purchase_order' => false,
                'signature_in_bills' => false,
                'signature_in_vendor_payments' => false,
                'signature_in_vendor_credits' => false,
                'signature_in_inventory' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
