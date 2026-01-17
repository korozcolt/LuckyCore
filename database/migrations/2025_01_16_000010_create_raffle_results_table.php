<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Raffle results table migration.
 *
 * @see REGLAS_NEGOCIO.md §6 - Resultado se registra manualmente (MVP)
 * @see REGLAS_NEGOCIO.md §6 - Formula de ganador definida y almacenada (auditable)
 * @see PANTALLAS.md §B6 - Registrar resultado, Calcular ganador, Confirmar y publicar
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('raffle_results', function (Blueprint $table) {
            $table->id();
            $table->ulid('ulid')->unique();

            $table->foreignId('raffle_id')->constrained()->cascadeOnDelete();

            // Lottery result data
            $table->string('lottery_name')->nullable();
            $table->string('lottery_number'); // The winning number from lottery
            $table->date('lottery_date');

            // Winner calculation
            $table->text('calculation_formula')->nullable(); // How winner was calculated
            $table->json('calculation_details')->nullable(); // Step by step calculation

            // Status
            $table->boolean('is_confirmed')->default(false);
            $table->boolean('is_published')->default(false);
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('published_at')->nullable();

            // Audit
            $table->foreignId('registered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index(['raffle_id', 'is_published']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('raffle_results');
    }
};
