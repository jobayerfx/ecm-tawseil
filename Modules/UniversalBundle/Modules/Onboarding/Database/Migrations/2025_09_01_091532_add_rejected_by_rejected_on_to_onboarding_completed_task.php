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
        // Add rejected_by column if it doesn't exist
        if (!Schema::hasColumn('onboarding_completed_task', 'rejected_by')) {
            Schema::table('onboarding_completed_task', function (Blueprint $table) {
                $table->integer('rejected_by')->unsigned()->nullable()->after('rejection_reason');
                $table->foreign('rejected_by')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
            });
        }

        // Add rejected_on column if it doesn't exist
        if (!Schema::hasColumn('onboarding_completed_task', 'rejected_on')) {
            Schema::table('onboarding_completed_task', function (Blueprint $table) {
                $table->timestamp('rejected_on')->nullable()->after('rejected_by');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove added columns if they exist
        if (Schema::hasColumn('onboarding_completed_task', 'rejected_on')) {
            Schema::table('onboarding_completed_task', function (Blueprint $table) {
                $table->dropColumn('rejected_on');
            });
        }

        if (Schema::hasColumn('onboarding_completed_task', 'rejected_by')) {
            Schema::table('onboarding_completed_task', function (Blueprint $table) {
                $table->dropForeign(['rejected_by']);
                $table->dropColumn('rejected_by');
            });
        }
    }
};
