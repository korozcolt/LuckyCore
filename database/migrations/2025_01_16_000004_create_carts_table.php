<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Carts and cart_items tables migration.
 *
 * @see REGLAS_NEGOCIO.md §1 - Carrito existe para invitado (session_id) y usuario (user_id)
 * @see REGLAS_NEGOCIO.md §1 - Al login: merge de carrito sesion -> usuario
 * @see PANTALLAS.md §A4 - Carrito (multi-sorteo)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->ulid('ulid')->unique();

            // Either session_id OR user_id (never both for active cart)
            $table->string('session_id')->nullable()->index();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            // Metadata
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            // Timestamps
            $table->timestamp('merged_at')->nullable(); // when guest cart was merged
            $table->timestamp('converted_at')->nullable(); // when converted to order
            $table->timestamps();

            // Ensure uniqueness: one active cart per session or user
            $table->unique(['session_id', 'converted_at']);
            $table->index(['user_id', 'converted_at']);
        });

        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained()->cascadeOnDelete();
            $table->foreignId('raffle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('raffle_package_id')->nullable()->constrained()->nullOnDelete();

            $table->unsignedInteger('quantity');
            $table->unsignedInteger('unit_price'); // price at time of adding (in cents)

            $table->timestamps();

            // One item per raffle per cart
            $table->unique(['cart_id', 'raffle_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('carts');
    }
};
