<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create raffle_prizes table for multiple prizes per raffle.
 *
 * @see REGLAS_NEGOCIO.md §6 - Premios múltiples
 * @see ANALISIS_REGLAS_NEGOCIO.md §3 - Premios Múltiples con Combinaciones
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('raffle_prizes', function (Blueprint $table) {
            $table->id();
            $table->ulid('ulid')->unique();
            $table->foreignId('raffle_id')->constrained()->cascadeOnDelete();

            // Prize details
            $table->string('name'); // ej: "Primer Premio", "Premio Especial"
            $table->text('description')->nullable();
            $table->unsignedBigInteger('prize_value')->default(0); // Value in cents
            $table->unsignedInteger('prize_position')->default(1); // Order: 1, 2, 3...

            // Winning conditions (JSON)
            // Structure:
            // {
            //   "type": "exact_match|reverse|permutation|last_digits|first_digits|combination",
            //   "match_value": "12345" (for exact_match, reverse),
            //   "digit_count": 2 (for last_digits, first_digits),
            //   "combinations": [
            //     {"digits": [1, 2, 3], "order": "any|exact|reverse"}
            //   ]
            // }
            $table->json('winning_conditions')->nullable();

            // Status and ordering
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            // Indexes
            $table->index(['raffle_id', 'is_active']);
            $table->index(['raffle_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('raffle_prizes');
    }
};
