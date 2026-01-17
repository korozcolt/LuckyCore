<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Raffle packages table migration.
 *
 * @see PANTALLAS.md §A3 - Botones de paquetes (ej: 50/70/100/120 recomendado)
 * @see PANTALLAS.md §B2 - paquetes + recomendado
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('raffle_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('raffle_id')->constrained()->cascadeOnDelete();

            $table->string('name');
            $table->unsignedInteger('quantity'); // number of tickets
            $table->unsignedInteger('price'); // total price in cents (can have discount)
            $table->boolean('is_recommended')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['raffle_id', 'is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('raffle_packages');
    }
};
