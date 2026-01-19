<?php

use App\Enums\RaffleStatus;
use App\Livewire\Pages\Raffles\Show as RaffleShow;
use App\Livewire\Pages\Winners;
use App\Models\Raffle;
use App\Models\RaffleResult;
use App\Models\Winner;
use App\Models\WinnerTestimonial;
use Livewire\Livewire;

describe('Winners Page', function () {
    describe('index page', function () {
        it('renders the winners page', function () {
            $this->get(route('winners'))
                ->assertStatus(200)
                ->assertSeeLivewire(Winners::class);
        });

        it('shows published winners', function () {
            $raffle = Raffle::factory()->create([
                'status' => RaffleStatus::Completed,
                'title' => 'Sorteo Test',
            ]);

            $winner = Winner::factory()->published()->create([
                'raffle_id' => $raffle->id,
                'winner_name' => 'Juan Pérez',
                'prize_name' => 'Primer Premio',
            ]);

            Livewire::test(Winners::class)
                ->assertSee('Juan P.')
                ->assertSee('Primer Premio')
                ->assertSee('Sorteo Test');
        });

        it('does not show unpublished winners', function () {
            $winner = Winner::factory()->unpublished()->create([
                'winner_name' => 'Hidden Winner',
                'prize_name' => 'Secret Prize',
            ]);

            Livewire::test(Winners::class)
                ->assertDontSee('Hidden Winner')
                ->assertDontSee('Secret Prize');
        });

        it('can filter by raffle', function () {
            $raffle1 = Raffle::factory()->create([
                'status' => RaffleStatus::Completed,
                'title' => 'Raffle One',
            ]);

            $raffle2 = Raffle::factory()->create([
                'status' => RaffleStatus::Completed,
                'title' => 'Raffle Two',
            ]);

            $winner1 = Winner::factory()->published()->create([
                'raffle_id' => $raffle1->id,
                'winner_name' => 'Winner One',
            ]);

            $winner2 = Winner::factory()->published()->create([
                'raffle_id' => $raffle2->id,
                'winner_name' => 'Winner Two',
            ]);

            Livewire::test(Winners::class)
                ->assertSee('Winner O.') // Both visible initially
                ->call('selectRaffle', $raffle1->id)
                ->assertSet('selectedRaffleId', $raffle1->id);
        });

        it('shows featured testimonials', function () {
            $winner = Winner::factory()->published()->create();

            $testimonial = WinnerTestimonial::factory()->featured()->create([
                'winner_id' => $winner->id,
                'comment' => 'Este sorteo fue increíble',
            ]);

            Livewire::test(Winners::class)
                ->assertSee('Este sorteo fue increíble');
        });

        it('does not show unapproved testimonials', function () {
            $winner = Winner::factory()->published()->create();

            $testimonial = WinnerTestimonial::factory()->pending()->create([
                'winner_id' => $winner->id,
                'comment' => 'Pending testimonial content',
            ]);

            Livewire::test(Winners::class)
                ->assertDontSee('Pending testimonial content');
        });

        it('shows empty state when no winners', function () {
            Livewire::test(Winners::class)
                ->assertSee('Aún no hay ganadores');
        });
    });
});

describe('Raffle Show Page with Winners', function () {
    it('shows winners section on completed raffle', function () {
        $raffle = Raffle::factory()->create([
            'status' => RaffleStatus::Completed,
            'title' => 'Completed Raffle',
        ]);

        $result = RaffleResult::factory()->published()->create([
            'raffle_id' => $raffle->id,
            'lottery_number' => '12345',
        ]);

        $winner = Winner::factory()->published()->create([
            'raffle_id' => $raffle->id,
            'winner_name' => 'Carlos García',
            'prize_name' => 'Primer Premio',
            'ticket_number' => '12345',
        ]);

        Livewire::test(RaffleShow::class, ['raffle' => $raffle])
            ->assertSee('Ganadores del Sorteo')
            ->assertSee('Carlos G.')
            ->assertSee('Primer Premio')
            ->assertSee('12345');
    });

    it('shows winning number on completed raffle', function () {
        $raffle = Raffle::factory()->create([
            'status' => RaffleStatus::Completed,
        ]);

        $result = RaffleResult::factory()->published()->create([
            'raffle_id' => $raffle->id,
            'lottery_number' => '54321',
            'lottery_name' => 'Lotería de Bogotá',
        ]);

        $winner = Winner::factory()->published()->create([
            'raffle_id' => $raffle->id,
        ]);

        // Refresh raffle to load the result relationship
        $raffle->refresh();

        Livewire::test(RaffleShow::class, ['raffle' => $raffle])
            ->assertSee('54321')
            ->assertSee('Lotería de Bogotá');
    });

    it('does not show winners section on active raffle', function () {
        $raffle = Raffle::factory()->create([
            'status' => RaffleStatus::Active,
        ]);

        Livewire::test(RaffleShow::class, ['raffle' => $raffle])
            ->assertDontSee('Ganadores del Sorteo');
    });

    it('shows winner testimonial when approved', function () {
        $raffle = Raffle::factory()->create([
            'status' => RaffleStatus::Completed,
        ]);

        $result = RaffleResult::factory()->published()->create([
            'raffle_id' => $raffle->id,
        ]);

        $winner = Winner::factory()->published()->create([
            'raffle_id' => $raffle->id,
        ]);

        $testimonial = WinnerTestimonial::factory()->approved()->create([
            'winner_id' => $winner->id,
            'comment' => 'Gracias por este premio tan increíble!',
            'rating' => 5,
        ]);

        Livewire::test(RaffleShow::class, ['raffle' => $raffle])
            ->assertSee('Gracias por este premio tan increíble!');
    });
});

describe('WinnerTestimonial model', function () {
    it('can be approved by moderator', function () {
        $moderator = \App\Models\User::factory()->create();
        $testimonial = WinnerTestimonial::factory()->pending()->create();

        expect($testimonial->is_pending)->toBeTrue();

        $testimonial->approve($moderator->id);

        expect($testimonial->fresh())
            ->is_approved->toBeTrue()
            ->moderated_by->toBe($moderator->id)
            ->moderated_at->not->toBeNull();
    });

    it('can be rejected with reason', function () {
        $moderator = \App\Models\User::factory()->create();
        $testimonial = WinnerTestimonial::factory()->pending()->create();

        $testimonial->reject($moderator->id, 'Contenido inapropiado');

        expect($testimonial->fresh())
            ->is_rejected->toBeTrue()
            ->rejection_reason->toBe('Contenido inapropiado');
    });

    it('shows display name with privacy by default', function () {
        $winner = Winner::factory()->create([
            'winner_name' => 'María González',
        ]);

        $testimonial = WinnerTestimonial::factory()->create([
            'winner_id' => $winner->id,
            'show_full_name' => false,
        ]);

        expect($testimonial->display_name)->toBe('María G.');
    });

    it('shows full name when allowed', function () {
        $winner = Winner::factory()->create([
            'winner_name' => 'María González',
        ]);

        $testimonial = WinnerTestimonial::factory()->showFullName()->create([
            'winner_id' => $winner->id,
        ]);

        expect($testimonial->display_name)->toBe('María González');
    });
});
