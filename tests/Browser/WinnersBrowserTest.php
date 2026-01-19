<?php

use App\Enums\RaffleStatus;
use App\Models\Raffle;
use App\Models\RaffleResult;
use App\Models\Winner;
use App\Models\WinnerTestimonial;

describe('Winners Page Browser Tests', function () {
    it('shows winners page with published winners', function () {
        $raffle = Raffle::factory()->create([
            'status' => RaffleStatus::Completed,
            'title' => 'Sorteo Gran Premio',
        ]);

        RaffleResult::factory()->published()->create([
            'raffle_id' => $raffle->id,
            'lottery_number' => '12345',
        ]);

        $winner = Winner::factory()->published()->firstPrize()->create([
            'raffle_id' => $raffle->id,
            'winner_name' => 'Carlos Rodríguez',
            'prize_name' => 'Premio Mayor',
            'prize_value' => 50000000,
        ]);

        $page = visit('/ganadores');

        $page->assertSee('Ganadores')
            ->assertSee('Carlos R.')
            ->assertSee('Premio Mayor')
            ->assertSee('Sorteo Gran Premio')
            ->assertNoJavaScriptErrors();
    });

    it('shows featured testimonials section', function () {
        $raffle = Raffle::factory()->create([
            'status' => RaffleStatus::Completed,
        ]);

        $winner = Winner::factory()->published()->create([
            'raffle_id' => $raffle->id,
        ]);

        WinnerTestimonial::factory()->featured()->create([
            'winner_id' => $winner->id,
            'comment' => 'Excelente experiencia con el sorteo',
            'rating' => 5,
        ]);

        $page = visit('/ganadores');

        $page->assertSee('Testimonios')
            ->assertSee('Excelente experiencia con el sorteo')
            ->assertNoJavaScriptErrors();
    });

    it('can filter winners by raffle', function () {
        $raffle1 = Raffle::factory()->create([
            'status' => RaffleStatus::Completed,
            'title' => 'Sorteo Alfa',
        ]);

        $raffle2 = Raffle::factory()->create([
            'status' => RaffleStatus::Completed,
            'title' => 'Sorteo Beta',
        ]);

        Winner::factory()->published()->create([
            'raffle_id' => $raffle1->id,
            'winner_name' => 'Ganador Alfa',
        ]);

        Winner::factory()->published()->create([
            'raffle_id' => $raffle2->id,
            'winner_name' => 'Ganador Beta',
        ]);

        $page = visit('/ganadores');

        $page->assertSee('Sorteo Alfa')
            ->assertSee('Sorteo Beta')
            ->assertNoJavaScriptErrors();

        // Click on filter button for Sorteo Alfa
        $page->click('Sorteo Alfa');

        // After filtering, should still show the page without errors
        $page->assertNoJavaScriptErrors();
    });

    it('shows empty state when no winners exist', function () {
        $page = visit('/ganadores');

        $page->assertSee('Aún no hay ganadores')
            ->assertNoJavaScriptErrors();
    });
});

describe('Completed Raffle Detail Browser Tests', function () {
    it('shows winners section on completed raffle page', function () {
        $raffle = Raffle::factory()->create([
            'status' => RaffleStatus::Completed,
            'title' => 'Sorteo Finalizado',
            'slug' => 'sorteo-finalizado',
        ]);

        $result = RaffleResult::factory()->published()->create([
            'raffle_id' => $raffle->id,
            'lottery_number' => '98765',
            'lottery_name' => 'Lotería de Bogotá',
        ]);

        $winner = Winner::factory()->published()->firstPrize()->create([
            'raffle_id' => $raffle->id,
            'winner_name' => 'Ana María López',
            'prize_name' => 'Gran Premio',
            'ticket_number' => '98765',
            'prize_value' => 100000000,
        ]);

        $page = visit('/sorteos/'.$raffle->slug);

        $page->assertSee('Sorteo Finalizado')
            ->assertSee('Ganadores del Sorteo')
            ->assertSee('Ana M.')
            ->assertSee('Gran Premio')
            ->assertSee('98765')
            ->assertSee('Lotería de Bogotá')
            ->assertNoJavaScriptErrors();
    });

    it('shows winner testimonial when approved', function () {
        $raffle = Raffle::factory()->create([
            'status' => RaffleStatus::Completed,
            'slug' => 'sorteo-con-testimonio',
        ]);

        $result = RaffleResult::factory()->published()->create([
            'raffle_id' => $raffle->id,
        ]);

        $winner = Winner::factory()->published()->create([
            'raffle_id' => $raffle->id,
        ]);

        WinnerTestimonial::factory()->approved()->create([
            'winner_id' => $winner->id,
            'comment' => 'Muchas gracias por este increíble premio',
            'rating' => 5,
        ]);

        $page = visit('/sorteos/'.$raffle->slug);

        $page->assertSee('Muchas gracias por este increíble premio')
            ->assertNoJavaScriptErrors();
    });

    it('shows link to all winners page', function () {
        $raffle = Raffle::factory()->create([
            'status' => RaffleStatus::Completed,
            'slug' => 'sorteo-link-ganadores',
        ]);

        $result = RaffleResult::factory()->published()->create([
            'raffle_id' => $raffle->id,
        ]);

        $winner = Winner::factory()->published()->create([
            'raffle_id' => $raffle->id,
        ]);

        $page = visit('/sorteos/'.$raffle->slug);

        $page->assertSee('Ver todos los ganadores')
            ->assertNoJavaScriptErrors();
    });

    it('does not show winners section on active raffle', function () {
        $raffle = Raffle::factory()->create([
            'status' => RaffleStatus::Active,
            'slug' => 'sorteo-activo',
        ]);

        $page = visit('/sorteos/'.$raffle->slug);

        $page->assertDontSee('Ganadores del Sorteo')
            ->assertNoJavaScriptErrors();
    });
});

describe('Smoke Tests', function () {
    it('winners page loads without errors', function () {
        $pages = visit(['/ganadores']);

        $pages->assertNoSmoke();
    });

    it('completed raffle page loads without errors', function () {
        $raffle = Raffle::factory()->create([
            'status' => RaffleStatus::Completed,
            'slug' => 'smoke-test-raffle',
        ]);

        RaffleResult::factory()->published()->create([
            'raffle_id' => $raffle->id,
        ]);

        Winner::factory()->published()->create([
            'raffle_id' => $raffle->id,
        ]);

        $pages = visit(['/sorteos/'.$raffle->slug]);

        $pages->assertNoSmoke();
    });
});
