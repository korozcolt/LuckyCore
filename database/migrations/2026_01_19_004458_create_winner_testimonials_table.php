<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('winner_testimonials', function (Blueprint $table) {
            $table->id();
            $table->ulid('ulid')->unique();
            $table->foreignId('winner_id')->constrained()->cascadeOnDelete();

            // Testimonial content
            $table->text('comment')->nullable();
            $table->string('photo_path')->nullable();
            $table->unsignedTinyInteger('rating')->nullable(); // 1-5 stars optional

            // Moderation
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->foreignId('moderated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('moderated_at')->nullable();

            // Display options
            $table->boolean('show_full_name')->default(false); // Privacy: show "Juan P." vs "Juan Pérez"
            $table->boolean('is_featured')->default(false); // Show in homepage carousel

            $table->timestamps();

            // Indexes
            $table->index('status');
            $table->index(['status', 'is_featured']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('winner_testimonials');
    }
};
