<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Raffle images table migration.
 *
 * @see PANTALLAS.md §A3 - Galeria (slider)
 * @see PANTALLAS.md §B2 - imagenes
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('raffle_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('raffle_id')->constrained()->cascadeOnDelete();

            $table->string('path');
            $table->string('disk')->default('public');
            $table->string('alt_text')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_primary')->default(false);

            $table->timestamps();

            $table->index(['raffle_id', 'sort_order']);
            $table->index(['raffle_id', 'is_primary']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('raffle_images');
    }
};
