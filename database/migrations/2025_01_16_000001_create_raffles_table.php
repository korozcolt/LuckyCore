<?php

use App\Enums\RaffleStatus;
use App\Enums\TicketAssignmentMethod;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Raffles table migration.
 *
 * @see PANTALLAS.md §B2 - Crear/editar sorteos
 * @see REGLAS_NEGOCIO.md §4-5 - Tickets y Stock
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('raffles', function (Blueprint $table) {
            $table->id();
            $table->ulid('ulid')->unique();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->text('short_description')->nullable();

            // Pricing and stock
            $table->unsignedInteger('ticket_price'); // in cents
            $table->unsignedInteger('total_tickets');
            $table->unsignedInteger('sold_tickets')->default(0);

            // Purchase rules
            $table->unsignedInteger('min_purchase_qty')->default(1);
            $table->unsignedInteger('max_purchase_qty')->nullable();
            $table->unsignedInteger('max_per_user')->nullable();
            $table->boolean('allow_custom_quantity')->default(false);
            $table->unsignedInteger('quantity_step')->default(1);

            // Ticket assignment method
            $table->string('ticket_assignment_method')->default(TicketAssignmentMethod::Random->value);

            // Status and dates
            $table->string('status')->default(RaffleStatus::Draft->value);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('draw_at')->nullable();

            // Lottery/result source
            $table->string('lottery_source')->nullable();
            $table->string('lottery_reference')->nullable();

            // SEO and display
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('featured')->default(false);

            $table->timestamps();
            $table->softDeletes();

            // Indexes for common queries
            $table->index('status');
            $table->index('starts_at');
            $table->index('ends_at');
            $table->index('featured');
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('raffles');
    }
};
