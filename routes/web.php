<?php

use App\Livewire\Pages\Cart\Index as CartIndex;
use App\Livewire\Pages\Checkout\Index as CheckoutIndex;
use App\Livewire\Pages\Cms\Show as CmsShow;
use App\Livewire\Pages\Home;
use App\Livewire\Pages\Orders\Index as OrdersIndex;
use App\Livewire\Pages\Orders\Show as OrdersShow;
use App\Livewire\Pages\Payment\Index as PaymentIndex;
use App\Livewire\Pages\Raffles\Index as RafflesIndex;
use App\Livewire\Pages\Raffles\Show as RafflesShow;
use App\Livewire\Pages\Winners;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/favicon.ico', function () {
    $path = public_path('favicon.ico');

    abort_unless(is_file($path), 404);

    return response()->file($path, [
        'Content-Type' => 'image/x-icon',
    ]);
})->name('favicon');

Route::get('/', Home::class)->name('home');

// Raffles
Route::prefix('sorteos')->name('raffles.')->group(function () {
    Route::get('/', RafflesIndex::class)->name('index');
    Route::get('/{raffle:slug}', RafflesShow::class)->name('show');
});

// CMS Pages
Route::get('/pagina/{slug}', CmsShow::class)->name('page.show');

// Winners
Route::get('/ganadores', Winners::class)->name('winners');

// Cart
Route::get('/carrito', CartIndex::class)->name('cart');

// Checkout (requires cart with items)
Route::get('/checkout', CheckoutIndex::class)->name('checkout');

// Payment page (user selects payment method and pays)
Route::get('/pagar/{order:ulid}', PaymentIndex::class)->name('payment');

// Payment callbacks (public - user is redirected here after payment)
Route::get('/pagos/{provider}/callback/{order:ulid}', [App\Http\Controllers\PaymentCallbackController::class, 'callback'])
    ->name('payments.callback');

// Public order confirmation page (accessible by ulid for guests)
Route::get('/orden/{order:ulid}', OrdersShow::class)->name('orders.confirmation');

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        $user = auth()->user();

        if ($user instanceof \App\Models\User && ($user->isSupport() || $user->isAdmin() || $user->isSuperAdmin())) {
            return redirect('/admin');
        }

        return redirect()->route('orders.index');
    })->name('dashboard');

    // Orders (user's order history)
    Route::prefix('mis-compras')->name('orders.')->group(function () {
        Route::get('/', OrdersIndex::class)->name('index');
        Route::get('/{order}', OrdersShow::class)->name('show');
    });
});

require __DIR__.'/settings.php';
