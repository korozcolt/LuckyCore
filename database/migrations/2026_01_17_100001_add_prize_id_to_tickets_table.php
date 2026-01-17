<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add prize_id to tickets table to track which prize a ticket won.
 *
 * @see REGLAS_NEGOCIO.md §6 - Premios múltiples
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->foreignId('prize_id')
                ->nullable()
                ->after('prize_position')
                ->constrained('raffle_prizes')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropForeign(['prize_id']);
            $table->dropColumn('prize_id');
        });
    }
};
