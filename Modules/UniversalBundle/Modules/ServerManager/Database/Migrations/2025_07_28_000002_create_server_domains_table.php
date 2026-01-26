<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServerDomainsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('server_domains', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('company_id')->nullable();
            $table->string('domain_name');
            $table->string('domain_provider');
            $table->string('provider_url')->nullable();
            $table->string('domain_type')->default('com'); // com, net, org, etc.
            $table->string('registrar')->nullable();
            $table->string('registrar_url')->nullable();
            $table->string('registrar_username')->nullable();
            $table->text('registrar_password')->nullable(); // encrypted
            $table->string('registrar_status')->nullable();
            $table->unsignedInteger('project_id')->nullable();
            $table->unsignedInteger('client_id')->nullable();
            $table->date('registration_date');
            $table->date('expiry_date');
            $table->date('renewal_date')->nullable();
            $table->string('username')->nullable();
            $table->text('password')->nullable();
            $table->decimal('annual_cost', 10, 2)->default(0);
            $table->string('billing_cycle')->default('annually'); // monthly, quarterly, annually
            $table->string('status')->default('active'); // active, expired, suspended, transferred
            $table->string('dns_provider')->nullable();
            $table->string('dns_status')->nullable();
            $table->text('nameservers')->nullable(); // JSON array
            $table->text('dns_records')->nullable(); // JSON array
            $table->string('whois_protection')->default('disabled'); // enabled, disabled
            $table->string('auto_renewal')->default('disabled'); // enabled, disabled
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('hosting_id')->nullable(); // Link to hosting if applicable
            $table->boolean('expiry_notification')->default(false);
            $table->integer('notification_days_before')->nullable();
            $table->enum('notification_time_unit', ['days', 'weeks', 'months'])->default('days');
            $table->timestamp('last_notification_sent')->nullable();
            $table->unsignedInteger('assigned_to')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('hosting_id')->references('id')->on('server_hostings')->onDelete('set null');
            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('set null');
            $table->foreign('client_id')->references('id')->on('client_details')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('server_domains');
    }
}
