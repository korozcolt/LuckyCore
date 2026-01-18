<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Make user_id nullable to support guest orders.
 *
 * Tickets are still linked to an order and order_item, but guest checkout orders may not have a user_id.
 *
 * @see PANTALLAS.md §A8 - Mis compras (detalle orden)
 * @see REGLAS_NEGOCIO.md §4 - Se asignan SOLO al aprobar pago
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable(false)->change();
        });
    }
};
