<?php

use App\Livewire\Pages\Cart\Index as CartIndex;
use App\Livewire\Pages\Checkout\Index as CheckoutIndex;
use App\Livewire\Pages\Cms\Show as CmsShow;
use App\Livewire\Pages\Home;
use App\Livewire\Pages\Orders\Index as OrdersIndex;
use App\Livewire\Pages\Orders\Show as OrdersShow;
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

// Cart
Route::get('/carrito', CartIndex::class)->name('cart');

// Checkout (requires cart with items)
Route::get('/checkout', CheckoutIndex::class)->name('checkout');

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    // Orders
    Route::prefix('mis-compras')->name('orders.')->group(function () {
        Route::get('/', OrdersIndex::class)->name('index');
        Route::get('/{order}', OrdersShow::class)->name('show');
    });
});

require __DIR__.'/settings.php';
