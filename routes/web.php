<?php

use App\Livewire\Pages\Cms\Show as CmsShow;
use App\Livewire\Pages\Home;
use App\Livewire\Pages\Orders\Index as OrdersIndex;
use App\Livewire\Pages\Raffles\Index as RafflesIndex;
use App\Livewire\Pages\Raffles\Show as RafflesShow;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', Home::class)->name('home');

// Raffles
Route::prefix('sorteos')->name('raffles.')->group(function () {
    Route::get('/', RafflesIndex::class)->name('index');
    Route::get('/{raffle:slug}', RafflesShow::class)->name('show');
});

// CMS Pages
Route::get('/pagina/{slug}', CmsShow::class)->name('page.show');

// Cart (placeholder for Sprint 2)
Route::get('/carrito', fn () => view('livewire.pages.cart.index'))
    ->name('cart.index');

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    // Orders (placeholder for Sprint 2)
    Route::get('/mis-compras', OrdersIndex::class)
        ->name('orders.index');
});

require __DIR__.'/settings.php';
