<?php

namespace Database\Factories;

use App\Models\CmsPage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CmsPage>
 */
class CmsPageFactory extends Factory
{
    protected $model = CmsPage::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence(3);

        return [
            'slug' => Str::slug($title),
            'title' => $title,
            'content' => fake()->paragraphs(3, true),
            'sections' => null,
            'meta_title' => fake()->optional()->sentence(),
            'meta_description' => fake()->optional()->sentence(),
            'is_published' => false,
            'published_at' => null,
            'last_edited_by' => null,
        ];
    }

    /**
     * Set the page as published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => true,
            'published_at' => now(),
        ]);
    }

    /**
     * Set the page as draft (unpublished).
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => false,
            'published_at' => null,
        ]);
    }

    /**
     * Add FAQ sections to the page.
     */
    public function withFaqSections(int $count = 3): static
    {
        return $this->state(fn (array $attributes) => [
            'sections' => collect(range(1, $count))->map(fn () => [
                'question' => fake()->sentence().'?',
                'answer' => fake()->paragraph(),
            ])->toArray(),
        ]);
    }

    /**
     * Set the editor of the page.
     */
    public function editedBy(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'last_edited_by' => $user->id,
        ]);
    }

    /**
     * Create a "Cómo Funciona" page.
     */
    public function howItWorks(): static
    {
        return $this->state(fn (array $attributes) => [
            'slug' => CmsPage::SLUG_HOW_IT_WORKS,
            'title' => 'Cómo Funciona',
        ]);
    }

    /**
     * Create a "Términos y Condiciones" page.
     */
    public function terms(): static
    {
        return $this->state(fn (array $attributes) => [
            'slug' => CmsPage::SLUG_TERMS,
            'title' => 'Términos y Condiciones',
        ]);
    }

    /**
     * Create a "Preguntas Frecuentes" page.
     */
    public function faq(): static
    {
        return $this->state(fn (array $attributes) => [
            'slug' => CmsPage::SLUG_FAQ,
            'title' => 'Preguntas Frecuentes',
        ]);
    }
}
