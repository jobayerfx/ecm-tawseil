<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add approval/rejection fields to onboarding_completed_task table
        if (!Schema::hasColumn('onboarding_completed_task', 'submission_status')) {
            Schema::table('onboarding_completed_task', function (Blueprint $table) {
                $table->enum('submission_status', ['pending', 'submitted', 'approved', 'rejected'])->default('pending')->after('status');
            });
        }

        if (!Schema::hasColumn('onboarding_completed_task', 'submitted_on')) {
            Schema::table('onboarding_completed_task', function (Blueprint $table) {
                $table->timestamp('submitted_on')->nullable()->after('submission_status');
            });
        }

        if (!Schema::hasColumn('onboarding_completed_task', 'approved_by')) {
            Schema::table('onboarding_completed_task', function (Blueprint $table) {
                $table->integer('approved_by')->unsigned()->nullable()->after('submitted_on');
                $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
            });
        }

        if (!Schema::hasColumn('onboarding_completed_task', 'approved_on')) {
            Schema::table('onboarding_completed_task', function (Blueprint $table) {
                $table->timestamp('approved_on')->nullable()->after('approved_by');
            });
        }

        if (!Schema::hasColumn('onboarding_completed_task', 'rejection_reason')) {
            Schema::table('onboarding_completed_task', function (Blueprint $table) {
                $table->text('rejection_reason')->nullable()->after('approved_on');
            });
        }

        // Update existing completed tasks to have 'approved' submission status
        DB::table('onboarding_completed_task')
            ->where('status', 'completed')
            ->update(['submission_status' => 'approved']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove added columns if they exist
        if (Schema::hasColumn('onboarding_completed_task', 'rejection_reason')) {
            Schema::table('onboarding_completed_task', function (Blueprint $table) {
                $table->dropColumn('rejection_reason');
            });
        }

        if (Schema::hasColumn('onboarding_completed_task', 'approved_on')) {
            Schema::table('onboarding_completed_task', function (Blueprint $table) {
                $table->dropColumn('approved_on');
            });
        }

        if (Schema::hasColumn('onboarding_completed_task', 'approved_by')) {
            Schema::table('onboarding_completed_task', function (Blueprint $table) {
                $table->dropForeign(['approved_by']);
                $table->dropColumn('approved_by');
            });
        }

        if (Schema::hasColumn('onboarding_completed_task', 'submitted_on')) {
            Schema::table('onboarding_completed_task', function (Blueprint $table) {
                $table->dropColumn('submitted_on');
            });
        }

        if (Schema::hasColumn('onboarding_completed_task', 'submission_status')) {
            Schema::table('onboarding_completed_task', function (Blueprint $table) {
                $table->dropColumn('submission_status');
            });
        }
    }
};
