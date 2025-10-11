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
            $table->boolean('is_verified')->default(false)->after('email_verified_at');
            $table->timestamp('verified_at')->nullable()->after('is_verified');
            $table->string('google_id')->nullable()->after('verified_at');
            $table->string('apple_id')->nullable()->after('google_id');
            $table->enum('register_source', ['email', 'google', 'apple'])->default('email')->after('apple_id');
            
            $table->index(['google_id']);
            $table->index(['apple_id']);
            $table->index(['is_verified']);
        });
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