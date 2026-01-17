<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Services\CartService;
use Illuminate\Auth\Events\Login;

/**
 * Merge guest cart into user's cart when they log in.
 *
 * @see PANTALLAS.md §A7 - Evento especial: al login, merge carrito sesión → usuario
 */
class MergeGuestCartOnLogin
{
    public function __construct(
        protected CartService $cartService
    ) {}

    public function handle(Login $event): void
    {
        $sessionId = session()->getId();
        $user = $event->user;

        // Attempt to merge guest cart into user's cart
        $this->cartService->mergeGuestCart($user, $sessionId);
    }
}
