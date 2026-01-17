<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Add ticket number configuration fields to raffles table.
 *
 * @see REGLAS_NEGOCIO.md §4 - Configuración de números
 * @see ANALISIS_REGLAS_NEGOCIO.md §2 - Números de Tickets Configurables
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('raffles', function (Blueprint $table) {
            // Ticket number configuration
            $table->unsignedTinyInteger('ticket_digits')->default(5)->after('ticket_assignment_method');
            $table->unsignedBigInteger('ticket_min_number')->default(1)->after('ticket_digits');
            $table->unsignedBigInteger('ticket_max_number')->nullable()->after('ticket_min_number');
        });

        // Set default ticket_max_number for existing raffles based on ticket_digits
        // For 5 digits: max = 99999, for 6 digits: max = 999999, etc.
        // Use PHP to calculate since REPEAT is MySQL-specific
        $raffles = DB::table('raffles')
            ->whereNull('ticket_max_number')
            ->get(['id', 'ticket_digits']);

        foreach ($raffles as $raffle) {
            $digits = $raffle->ticket_digits ?? 5;
            $maxNumber = (int) str_repeat('9', $digits);
            DB::table('raffles')
                ->where('id', $raffle->id)
                ->update(['ticket_max_number' => $maxNumber]);
        }
    }

    public function down(): void
    {
        Schema::table('raffles', function (Blueprint $table) {
            $table->dropColumn(['ticket_digits', 'ticket_min_number', 'ticket_max_number']);
        });
    }
};
