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
        Schema::create('page_views', function (Blueprint $table) {
            $table->id();
            $table->string('path', 500)->index();
            $table->string('session_hash', 64)->index();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('ip_hash', 64)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->string('referrer', 500)->nullable();
            $table->string('device_type', 20)->default('desktop');
            $table->timestamp('created_at')->useCurrent()->index();
        });

        // Daily aggregated stats for efficient queries
        Schema::create('page_view_stats', function (Blueprint $table) {
            $table->id();
            $table->date('date')->index();
            $table->string('path', 500)->index();
            $table->unsignedInteger('views')->default(0);
            $table->unsignedInteger('unique_visitors')->default(0);
            $table->timestamps();

            $table->unique(['date', 'path']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('page_view_stats');
        Schema::dropIfExists('page_views');
    }
};
