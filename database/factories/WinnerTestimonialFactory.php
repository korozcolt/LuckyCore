<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Winner;
use App\Models\WinnerTestimonial;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WinnerTestimonial>
 */
class WinnerTestimonialFactory extends Factory
{
    protected $model = WinnerTestimonial::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'winner_id' => Winner::factory(),
            'comment' => $this->faker->paragraph(),
            'photo_path' => null,
            'rating' => $this->faker->numberBetween(4, 5),
            'status' => WinnerTestimonial::STATUS_PENDING,
            'rejection_reason' => null,
            'moderated_by' => null,
            'moderated_at' => null,
            'show_full_name' => false,
            'is_featured' => false,
        ];
    }

    /**
     * Indicate that the testimonial is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => WinnerTestimonial::STATUS_APPROVED,
            'moderated_by' => User::factory(),
            'moderated_at' => now(),
        ]);
    }

    /**
     * Indicate that the testimonial is rejected.
     */
    public function rejected(?string $reason = null): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => WinnerTestimonial::STATUS_REJECTED,
            'rejection_reason' => $reason ?? $this->faker->sentence(),
            'moderated_by' => User::factory(),
            'moderated_at' => now(),
        ]);
    }

    /**
     * Indicate that the testimonial is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => WinnerTestimonial::STATUS_PENDING,
            'moderated_by' => null,
            'moderated_at' => null,
        ]);
    }

    /**
     * Indicate that the testimonial is featured.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
            'status' => WinnerTestimonial::STATUS_APPROVED,
        ]);
    }

    /**
     * Indicate that the testimonial shows full name.
     */
    public function showFullName(): static
    {
        return $this->state(fn (array $attributes) => [
            'show_full_name' => true,
        ]);
    }

    /**
     * Indicate that the testimonial has a photo.
     */
    public function withPhoto(): static
    {
        return $this->state(fn (array $attributes) => [
            'photo_path' => 'testimonials/'.$this->faker->uuid().'.jpg',
        ]);
    }
}
