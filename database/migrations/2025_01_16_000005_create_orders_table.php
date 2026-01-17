<?php

use App\Enums\OrderStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Orders and order_items tables migration.
 *
 * @see REGLAS_NEGOCIO.md §2 - Orden se crea desde carrito (order + order_items)
 * @see REGLAS_NEGOCIO.md §2 - Se congelan snapshots de precio por item
 * @see PANTALLAS.md §A6 - Mostrar "Codigo de soporte" (order_id/correlation)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->ulid('ulid')->unique();
            $table->string('order_number')->unique(); // Human-readable order number

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cart_id')->nullable()->constrained()->nullOnDelete();

            // Amounts (in cents)
            $table->unsignedInteger('subtotal');
            $table->unsignedInteger('total');

            // Status
            $table->string('status')->default(OrderStatus::Pending->value);

            // Support/traceability
            $table->string('support_code')->unique(); // Short code for customer support
            $table->uuid('correlation_id')->index(); // For tracking across systems

            // Customer snapshot at order time
            $table->string('customer_email');
            $table->string('customer_name')->nullable();
            $table->string('customer_phone')->nullable();

            // Metadata
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->boolean('terms_accepted')->default(false);
            $table->timestamp('terms_accepted_at')->nullable();

            // Payment tracking
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expired_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('status');
            $table->index('customer_email');
            $table->index('created_at');
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('raffle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('raffle_package_id')->nullable()->constrained()->nullOnDelete();

            // Snapshot at order time
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('unit_price'); // in cents
            $table->unsignedInteger('subtotal'); // quantity * unit_price in cents
            $table->string('raffle_title'); // snapshot of raffle title

            // Ticket tracking
            $table->unsignedInteger('tickets_assigned')->default(0);
            $table->boolean('tickets_complete')->default(false);

            $table->timestamps();

            $table->unique(['order_id', 'raffle_id']);
            $table->index('raffle_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
