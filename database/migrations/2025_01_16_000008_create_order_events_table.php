<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Order events (timeline) table migration.
 *
 * @see REGLAS_NEGOCIO.md §7 - Cada orden tiene timeline
 * @see REGLAS_NEGOCIO.md §7 - Events: order.created, payment.intent_created, webhook.received, etc.
 * @see ARQUITECTURA.md §3 - Trazabilidad obligatoria: order_events
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_events', function (Blueprint $table) {
            $table->id();
            $table->ulid('ulid')->unique();

            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_transaction_id')->nullable()->constrained()->nullOnDelete();

            // Event type (e.g., order.created, payment.intent_created, webhook.received)
            $table->string('event_type');

            // Event data
            $table->string('description')->nullable();
            $table->json('metadata')->nullable();

            // Error tracking for failed events
            $table->boolean('is_error')->default(false);
            $table->string('error_code')->nullable();
            $table->text('error_message')->nullable();

            // Actor (who/what triggered this event)
            $table->string('actor_type')->nullable(); // system, user, admin, webhook
            $table->unsignedBigInteger('actor_id')->nullable();

            // Correlation for distributed tracing
            $table->uuid('correlation_id')->nullable()->index();

            $table->timestamp('created_at');

            // Indexes
            $table->index('event_type');
            $table->index(['order_id', 'created_at']);
            $table->index('is_error');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_events');
    }
};
