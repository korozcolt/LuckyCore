<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tickets table migration.
 *
 * @see REGLAS_NEGOCIO.md §4 - Se asignan SOLO al aprobar pago
 * @see REGLAS_NEGOCIO.md §4 - Tickets deben ser unicos por sorteo (UNIQUE raffle_id+code)
 * @see ALCANCE.md §3 - Visualizacion de tickets en "Mis compras"
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->ulid('ulid')->unique();

            $table->foreignId('raffle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Ticket code - unique per raffle
            $table->string('code', 20);

            // Winner tracking
            $table->boolean('is_winner')->default(false);
            $table->unsignedTinyInteger('prize_position')->nullable(); // 1st, 2nd, 3rd place etc.
            $table->timestamp('won_at')->nullable();

            $table->timestamps();

            // CRITICAL: Tickets must be unique per raffle
            $table->unique(['raffle_id', 'code']);

            // Indexes for common queries
            $table->index('user_id');
            $table->index('order_id');
            $table->index(['raffle_id', 'is_winner']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
