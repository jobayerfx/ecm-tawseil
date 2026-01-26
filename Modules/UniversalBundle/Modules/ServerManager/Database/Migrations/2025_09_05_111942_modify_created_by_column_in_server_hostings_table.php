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
        try {
            Schema::table('server_hostings', function (Blueprint $table) {
                $table->dropForeign(['created_by']);
                $table->unsignedInteger('created_by')->nullable()->change();
                $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            });
        } catch (\Throwable $th) {
        }

        try {
            Schema::table('server_domains', function (Blueprint $table) {
                $table->dropForeign(['created_by']);
                $table->unsignedInteger('created_by')->nullable()->change();
                $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            });
        } catch (\Throwable $th) {
        }

        try {
            Schema::table('server_logs', function (Blueprint $table) {
                $table->dropForeign(['performed_by']);
                $table->unsignedInteger('performed_by')->nullable()->change();
                $table->foreign('performed_by')->references('id')->on('users')->onDelete('set null');
            });
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('server_hostings', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->unsignedInteger('created_by')->nullable(false)->change();
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::table('server_domains', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->unsignedInteger('created_by')->nullable(false)->change();
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::table('server_logs', function (Blueprint $table) {
            $table->dropForeign(['performed_by']);
            $table->unsignedInteger('performed_by')->nullable(false)->change();
            $table->foreign('performed_by')->references('id')->on('users')->onDelete('cascade');
        });
    }
};
