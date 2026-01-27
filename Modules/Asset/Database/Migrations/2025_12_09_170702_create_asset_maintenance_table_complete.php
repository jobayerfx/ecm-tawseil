<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Drop table if exists
        Schema::dropIfExists('asset_maintenance');

        // Create table with all columns
        Schema::create('asset_maintenance', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->nullable();
            
            // Asset relation
            $table->unsignedBigInteger('asset_id');
            $table->foreign('asset_id')->references('id')->on('assets')->onUpdate('cascade')->onDelete('cascade');
            
            // Basic fields
            $table->enum('type', ['planned', 'reactive'])->default('planned');
            $table->string('title', 255);
            $table->text('description')->nullable();
            
            // Status and priority
            $table->enum('status', ['scheduled', 'inprogress', 'completed', 'overdue','cancelled'])->default('scheduled');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            
            // Date fields (as datetime to match database)
            $table->datetime('scheduled_date');
            $table->datetime('due_date')->nullable();
            $table->datetime('started_at')->nullable();
            $table->datetime('completed_at')->nullable();
            
            // Assignment
            $table->integer('assigned_to')->unsigned()->nullable();
            $table->foreign('assigned_to')->references('id')->on('users')->onUpdate('cascade')->onDelete('set null');
            
            // User tracking
            $table->integer('added_by')->unsigned()->nullable();
            $table->foreign('added_by')->references('id')->on('users')->onUpdate('cascade')->onDelete('set null');
            $table->integer('created_by')->unsigned()->nullable();
            $table->foreign('created_by')->references('id')->on('users')->onUpdate('cascade')->onDelete('set null');
            
            // Notes
            $table->text('notes')->nullable();
            $table->text('completion_notes')->nullable();
            
            // Cost and vendor
            $table->decimal('cost', 10, 2)->nullable();
            $table->string('vendor', 191)->nullable();
            $table->text('parts_used')->nullable();
            
            // Recurring maintenance
            $table->boolean('is_recurring')->default(false);
            $table->enum('recurrence_type', ['daily', 'weekly', 'monthly', 'quarterly', 'yearly'])->nullable();
            $table->integer('recurrence_interval')->nullable();
            $table->date('next_due_date')->nullable();
            
            // Timestamps
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('asset_maintenance');
    }
};

