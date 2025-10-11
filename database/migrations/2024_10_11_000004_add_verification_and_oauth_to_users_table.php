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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'is_verified')) {
                $table->boolean('is_verified')->default(false)->after('email_verified_at');
            }
            if (!Schema::hasColumn('users', 'verified_at')) {
                $table->timestamp('verified_at')->nullable()->after('is_verified');
            }
            if (!Schema::hasColumn('users', 'google_id')) {
                $table->string('google_id')->nullable()->after('verified_at');
            }
            if (!Schema::hasColumn('users', 'apple_id')) {
                $table->string('apple_id')->nullable()->after('google_id');
            }
            
            // Update register_source enum if it exists but with different values
            if (Schema::hasColumn('users', 'register_source')) {
                // Skip adding register_source as it already exists
            } else {
                $table->enum('register_source', ['email', 'google', 'apple'])->default('email')->after('apple_id');
            }
        });
        
        // Add indexes if they don't exist
        try {
            Schema::table('users', function (Blueprint $table) {
                $table->index(['google_id']);
                $table->index(['apple_id']);
                $table->index(['is_verified']);
            });
        } catch (\Exception $e) {
            // Indexes might already exist, ignore
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['google_id']);
            $table->dropIndex(['apple_id']);
            $table->dropIndex(['is_verified']);
            
            $table->dropColumn([
                'is_verified',
                'verified_at',
                'google_id',
                'apple_id',
                'register_source'
            ]);
        });
    }
};