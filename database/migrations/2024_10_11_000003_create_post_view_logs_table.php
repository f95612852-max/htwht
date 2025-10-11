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
        Schema::create('post_view_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('status_id'); // post id
            $table->unsignedBigInteger('viewer_id')->nullable(); // user who viewed (null for anonymous)
            $table->string('ip_address', 45);
            $table->string('user_agent')->nullable();
            $table->timestamp('viewed_at')->useCurrent();
            $table->boolean('counted_for_earnings')->default(false);

            $table->foreign('status_id')->references('id')->on('statuses')->onDelete('cascade');
            $table->foreign('viewer_id')->references('id')->on('users')->onDelete('set null');
            
            // Prevent duplicate views from same IP within 24 hours
            $table->unique(['status_id', 'ip_address', 'viewer_id'], 'unique_view_per_day');
            $table->index(['status_id', 'viewed_at']);
            $table->index(['viewer_id', 'viewed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_view_logs');
    }
};