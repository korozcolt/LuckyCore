<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * CMS pages table migration.
 *
 * @see PANTALLAS.md §B5 - Editar como funciona / terminos / FAQ
 * @see ALCANCE.md §3 - CMS: Como funciona, Terminos, FAQ
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_pages', function (Blueprint $table) {
            $table->id();

            $table->string('slug')->unique();
            $table->string('title');
            $table->longText('content')->nullable();

            // For rich content sections
            $table->json('sections')->nullable();

            // SEO
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();

            // Status
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();

            // Audit
            $table->foreignId('last_edited_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index('is_published');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_pages');
    }
};
