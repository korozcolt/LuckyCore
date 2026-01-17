<?php

use App\Enums\PaymentProvider;
use App\Enums\PaymentStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Payment transactions table migration.
 *
 * @see REGLAS_NEGOCIO.md §3 - Pago valido SOLO si webhook verificado lo confirma
 * @see REGLAS_NEGOCIO.md §3 - Idempotencia: evento repetido NO duplica tickets
 * @see ARQUITECTURA.md §5 - Webhook endpoints por provider con firma
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->ulid('ulid')->unique();

            $table->foreignId('order_id')->constrained()->cascadeOnDelete();

            // Provider info
            $table->string('provider'); // wompi, mercadopago, epayco
            $table->string('provider_transaction_id')->nullable()->index();
            $table->string('provider_reference')->nullable(); // additional reference if needed

            // Amounts (in cents)
            $table->unsignedInteger('amount');
            $table->string('currency', 3)->default('COP');

            // Status
            $table->string('status')->default(PaymentStatus::Pending->value);

            // Idempotency
            $table->string('idempotency_key')->unique();
            $table->timestamp('webhook_received_at')->nullable();
            $table->unsignedInteger('webhook_attempts')->default(0);

            // Provider response data
            $table->json('provider_request')->nullable(); // what we sent
            $table->json('provider_response')->nullable(); // what we received
            $table->json('webhook_payload')->nullable(); // webhook data

            // Error tracking
            $table->string('error_code')->nullable();
            $table->text('error_message')->nullable();

            // Timestamps
            $table->timestamp('initiated_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('provider');
            $table->index('status');
            $table->index(['order_id', 'status']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
