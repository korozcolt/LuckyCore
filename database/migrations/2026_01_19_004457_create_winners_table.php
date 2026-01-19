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
        Schema::create('winners', function (Blueprint $table) {
            $table->id();
            $table->ulid('ulid')->unique();
            $table->foreignId('raffle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('raffle_prize_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            // Winner info (copied for historical record even if user deleted)
            $table->string('winner_name');
            $table->string('winner_email');
            $table->string('winner_phone')->nullable();
            $table->string('ticket_number', 20);

            // Prize info (copied for historical record)
            $table->string('prize_name');
            $table->unsignedBigInteger('prize_value');
            $table->unsignedTinyInteger('prize_position')->default(1);

            // Status tracking
            $table->boolean('is_notified')->default(false);
            $table->boolean('is_claimed')->default(false);
            $table->boolean('is_delivered')->default(false);
            $table->boolean('is_published')->default(true);

            // Timestamps
            $table->timestamp('notified_at')->nullable();
            $table->timestamp('claimed_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('published_at')->nullable();

            // Audit
            $table->foreignId('calculated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('delivered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('delivery_notes')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['raffle_id', 'is_published']);
            $table->index('is_published');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('winners');
    }
};
