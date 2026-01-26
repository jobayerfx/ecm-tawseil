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
        // Add indexes to users_chat table for better performance
        Schema::table('users_chat', function (Blueprint $table) {
            // Index for chat type queries
            $table->index(['chat_type', 'created_at'], 'idx_chat_type_created');
            
            // Index for private chat queries
            $table->index(['from', 'to', 'chat_type'], 'idx_private_chat');
            $table->index(['to', 'from', 'chat_type'], 'idx_private_chat_reverse');
            
            // Index for group chat queries
            $table->index(['group_id', 'chat_type', 'created_at'], 'idx_group_chat');
            
            // Index for channel chat queries
            $table->index(['channel_id', 'chat_type', 'created_at'], 'idx_channel_chat');
            
            // Index for unread messages
            $table->index(['to', 'message_seen', 'chat_type'], 'idx_unread_messages');
            
            // Index for company isolation
            $table->index(['company_id', 'created_at'], 'idx_company_created');
            
            // Full-text search index for message content
            $table->fullText(['message'], 'idx_message_fulltext');
        });

        // Add indexes to groups table
        Schema::table('groups', function (Blueprint $table) {
            $table->index(['company_id', 'owner_id'], 'idx_groups_company_owner');
            $table->index(['company_id', 'updated_at'], 'idx_groups_company_updated');
        });

        // Add indexes to group_members table
        Schema::table('group_members', function (Blueprint $table) {
            $table->index(['group_id', 'user_id'], 'idx_group_members_unique');
            $table->index(['user_id', 'group_id'], 'idx_user_groups');
        });

        // Add indexes to channels table
        Schema::table('channels', function (Blueprint $table) {
            $table->index(['company_id', 'owner_id'], 'idx_channels_company_owner');
            $table->index(['company_id', 'updated_at'], 'idx_channels_company_updated');
        });

        // Create message_files table if it doesn't exist
        if (!Schema::hasTable('message_files')) {
            Schema::create('message_files', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('userchat_id');
                $table->string('filename');
                $table->string('file_url');
                $table->string('file_type')->nullable();
                $table->unsignedBigInteger('file_size')->nullable();
                $table->timestamps();
                
                $table->foreign('userchat_id')->references('id')->on('users_chat')->onDelete('CASCADE');
                $table->index(['userchat_id'], 'idx_message_files_chat');
            });
        }

        // Create user_online_status table for better online tracking
        if (!Schema::hasTable('user_online_status')) {
            Schema::create('user_online_status', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('user_id');
                $table->enum('status', ['online', 'offline', 'away'])->default('offline');
                $table->timestamp('last_seen')->nullable();
                $table->string('session_id')->nullable();
                $table->timestamps();
                
                $table->foreign('user_id')->references('id')->on('users')->onDelete('CASCADE');
                $table->unique('user_id');
                $table->index(['status', 'last_seen'], 'idx_online_status');
            });
        }

        // Create message_notifications table for better notification management
        if (!Schema::hasTable('message_notifications')) {
            Schema::create('message_notifications', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('user_id');
                $table->string('type'); // private_message, group_message, channel_message, etc.
                $table->json('data');
                $table->boolean('is_read')->default(false);
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
                
                $table->foreign('user_id')->references('id')->on('users')->onDelete('CASCADE');
                $table->index(['user_id', 'is_read'], 'idx_notifications_user_read');
                $table->index(['type', 'created_at'], 'idx_notifications_type_created');
            });
        }

        // Create message_reactions table for future enhancement
        if (!Schema::hasTable('message_reactions')) {
            Schema::create('message_reactions', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('userchat_id');
                $table->unsignedInteger('user_id');
                $table->string('reaction'); // emoji or reaction type
                $table->timestamps();
                
                $table->foreign('userchat_id')->references('id')->on('users_chat')->onDelete('CASCADE');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('CASCADE');
                $table->unique(['userchat_id', 'user_id', 'reaction'], 'idx_reactions_unique');
                $table->index(['userchat_id', 'reaction'], 'idx_reactions_message');
            });
        }

        // Create message_threads table for future enhancement
        if (!Schema::hasTable('message_threads')) {
            Schema::create('message_threads', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('parent_message_id');
                $table->unsignedInteger('user_id');
                $table->text('message');
                $table->timestamps();
                
                $table->foreign('parent_message_id')->references('id')->on('users_chat')->onDelete('CASCADE');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('CASCADE');
                $table->index(['parent_message_id', 'created_at'], 'idx_threads_parent');
            });
        }

        // Add soft deletes to existing tables for better data management
        Schema::table('users_chat', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('groups', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('channels', function (Blueprint $table) {
            $table->softDeletes();
        });

        // Add additional columns for enhanced functionality
        Schema::table('users_chat', function (Blueprint $table) {
            $table->json('metadata')->nullable(); // For future extensibility
            $table->boolean('is_edited')->default(false);
            $table->timestamp('edited_at')->nullable();
            $table->unsignedInteger('edited_by')->nullable();
            $table->boolean('is_pinned')->default(false);
            $table->timestamp('pinned_at')->nullable();
            $table->unsignedInteger('pinned_by')->nullable();
        });

        Schema::table('groups', function (Blueprint $table) {
            $table->string('avatar')->nullable();
            $table->boolean('is_private')->default(false);
            $table->json('settings')->nullable();
            $table->unsignedInteger('max_members')->default(100);
        });

        Schema::table('channels', function (Blueprint $table) {
            $table->string('avatar')->nullable();
            $table->boolean('is_private')->default(false);
            $table->json('settings')->nullable();
            $table->unsignedInteger('max_members')->default(500);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove indexes from users_chat table
        Schema::table('users_chat', function (Blueprint $table) {
            $table->dropIndex('idx_chat_type_created');
            $table->dropIndex('idx_private_chat');
            $table->dropIndex('idx_private_chat_reverse');
            $table->dropIndex('idx_group_chat');
            $table->dropIndex('idx_channel_chat');
            $table->dropIndex('idx_unread_messages');
            $table->dropIndex('idx_company_created');
            $table->dropIndex('idx_message_fulltext');
            
            $table->dropSoftDeletes();
            $table->dropColumn(['metadata', 'is_edited', 'edited_at', 'edited_by', 'is_pinned', 'pinned_at', 'pinned_by']);
        });

        // Remove indexes from groups table
        Schema::table('groups', function (Blueprint $table) {
            $table->dropIndex('idx_groups_company_owner');
            $table->dropIndex('idx_groups_company_updated');
            
            $table->dropSoftDeletes();
            $table->dropColumn(['avatar', 'is_private', 'settings', 'max_members']);
        });

        // Remove indexes from group_members table
        Schema::table('group_members', function (Blueprint $table) {
            $table->dropIndex('idx_group_members_unique');
            $table->dropIndex('idx_user_groups');
        });

        // Remove indexes from channels table
        Schema::table('channels', function (Blueprint $table) {
            $table->dropIndex('idx_channels_company_owner');
            $table->dropIndex('idx_channels_company_updated');
            
            $table->dropSoftDeletes();
            $table->dropColumn(['avatar', 'is_private', 'settings', 'max_members']);
        });

        // Drop additional tables
        Schema::dropIfExists('message_reactions');
        Schema::dropIfExists('message_threads');
        Schema::dropIfExists('message_notifications');
        Schema::dropIfExists('user_online_status');
        Schema::dropIfExists('message_files');
    }
};
