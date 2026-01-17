<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\RaffleStatus;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Raffle;
use App\Models\RafflePackage;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Service for managing shopping cart operations.
 *
 * @see PANTALLAS.md §A4 - Carrito (multi-sorteo)
 */
class CartService
{
    /**
     * Get or create an active cart for the current session/user.
     */
    public function getOrCreateCart(?User $user = null, ?string $sessionId = null): Cart
    {
        // First, try to find existing cart
        $cart = $this->findActiveCart($user, $sessionId);

        if ($cart) {
            return $cart;
        }

        // Create new cart
        return Cart::create([
            'user_id' => $user?->id,
            'session_id' => $sessionId ?? session()->getId(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Find an active cart for user or session.
     */
    public function findActiveCart(?User $user = null, ?string $sessionId = null): ?Cart
    {
        if ($user) {
            return Cart::forUser($user->id)->with('items.raffle', 'items.package')->first();
        }

        if ($sessionId) {
            return Cart::forSession($sessionId)->with('items.raffle', 'items.package')->first();
        }

        return null;
    }

    /**
     * Add an item to the cart.
     *
     * @throws \InvalidArgumentException
     */
    public function addItem(
        Cart $cart,
        Raffle $raffle,
        int $quantity,
        ?RafflePackage $package = null
    ): CartItem {
        // Validate raffle is purchasable
        $this->validateRafflePurchasable($raffle);

        // Validate quantity
        $this->validateQuantity($raffle, $quantity, $package);

        // Calculate unit price
        $unitPrice = $package
            ? (int) ($package->price / $package->quantity)
            : $raffle->ticket_price;

        // Check if item already exists in cart
        $existingItem = $cart->getItem($raffle->id);

        if ($existingItem) {
            // Update existing item
            return $this->updateItem($existingItem, $quantity, $package);
        }

        // Create new item
        return CartItem::create([
            'cart_id' => $cart->id,
            'raffle_id' => $raffle->id,
            'raffle_package_id' => $package?->id,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
        ]);
    }

    /**
     * Update an existing cart item.
     */
    public function updateItem(
        CartItem $item,
        int $quantity,
        ?RafflePackage $package = null
    ): CartItem {
        $raffle = $item->raffle;

        // Validate
        $this->validateRafflePurchasable($raffle);
        $this->validateQuantity($raffle, $quantity, $package);

        // Calculate unit price
        $unitPrice = $package
            ? (int) ($package->price / $package->quantity)
            : $raffle->ticket_price;

        $item->update([
            'raffle_package_id' => $package?->id,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
        ]);

        return $item->fresh();
    }

    /**
     * Remove an item from the cart.
     */
    public function removeItem(CartItem $item): bool
    {
        return $item->delete();
    }

    /**
     * Clear all items from a cart.
     */
    public function clearCart(Cart $cart): void
    {
        $cart->items()->delete();
    }

    /**
     * Merge a guest cart into a user's cart after login.
     *
     * @see PANTALLAS.md §A7 - Evento especial: al login, merge carrito sesión → usuario
     */
    public function mergeGuestCart(User $user, string $sessionId): ?Cart
    {
        $guestCart = Cart::forSession($sessionId)->with('items')->first();

        if (! $guestCart || $guestCart->isEmpty()) {
            return null;
        }

        $userCart = $this->getOrCreateCart($user);

        return DB::transaction(function () use ($guestCart, $userCart) {
            foreach ($guestCart->items as $guestItem) {
                $existingItem = $userCart->getItem($guestItem->raffle_id);

                if ($existingItem) {
                    // Update existing item with larger quantity
                    if ($guestItem->quantity > $existingItem->quantity) {
                        $existingItem->update([
                            'quantity' => $guestItem->quantity,
                            'raffle_package_id' => $guestItem->raffle_package_id,
                            'unit_price' => $guestItem->unit_price,
                        ]);
                    }
                } else {
                    // Move item to user cart
                    $guestItem->update(['cart_id' => $userCart->id]);
                }
            }

            // Mark guest cart as merged
            $guestCart->update(['merged_at' => now()]);

            // Reload user cart with items
            return $userCart->fresh(['items.raffle', 'items.package']);
        });
    }

    /**
     * Validate that a raffle can be purchased.
     *
     * @throws \InvalidArgumentException
     */
    protected function validateRafflePurchasable(Raffle $raffle): void
    {
        if ($raffle->status !== RaffleStatus::Active) {
            throw new \InvalidArgumentException(
                "El sorteo '{$raffle->title}' no está activo para compra."
            );
        }

        if ($raffle->available_tickets <= 0) {
            throw new \InvalidArgumentException(
                "El sorteo '{$raffle->title}' no tiene boletos disponibles."
            );
        }
    }

    /**
     * Validate purchase quantity against raffle rules.
     *
     * @throws \InvalidArgumentException
     */
    protected function validateQuantity(
        Raffle $raffle,
        int $quantity,
        ?RafflePackage $package = null
    ): void {
        if ($quantity < 1) {
            throw new \InvalidArgumentException('La cantidad debe ser al menos 1.');
        }

        // If using a package, quantity must match package quantity
        if ($package && $quantity !== $package->quantity) {
            throw new \InvalidArgumentException(
                "La cantidad debe ser {$package->quantity} para el paquete seleccionado."
            );
        }

        // Check min purchase quantity
        if ($quantity < $raffle->min_purchase_qty) {
            throw new \InvalidArgumentException(
                "La cantidad mínima de compra es {$raffle->min_purchase_qty} boletos."
            );
        }

        // Check max purchase quantity
        if ($raffle->max_purchase_qty && $quantity > $raffle->max_purchase_qty) {
            throw new \InvalidArgumentException(
                "La cantidad máxima de compra es {$raffle->max_purchase_qty} boletos."
            );
        }

        // Check quantity step
        if ($raffle->quantity_step > 1 && ! $package) {
            $diff = $quantity - $raffle->min_purchase_qty;
            if ($diff % $raffle->quantity_step !== 0) {
                throw new \InvalidArgumentException(
                    "La cantidad debe ser en incrementos de {$raffle->quantity_step}."
                );
            }
        }

        // Check available stock
        if ($quantity > $raffle->available_tickets) {
            throw new \InvalidArgumentException(
                "Solo quedan {$raffle->available_tickets} boletos disponibles."
            );
        }
    }

    /**
     * Validate entire cart before checkout.
     *
     * @return array<string, string> Validation errors by raffle slug
     */
    public function validateCart(Cart $cart): array
    {
        $errors = [];
        $cart->load('items.raffle');

        foreach ($cart->items as $item) {
            try {
                $this->validateRafflePurchasable($item->raffle);
                $this->validateQuantity($item->raffle, $item->quantity, $item->package);
            } catch (\InvalidArgumentException $e) {
                $errors[$item->raffle->slug] = $e->getMessage();
            }
        }

        return $errors;
    }

    /**
     * Get cart item count for display in header.
     */
    public function getItemCount(?User $user = null, ?string $sessionId = null): int
    {
        $cart = $this->findActiveCart($user, $sessionId);

        return $cart ? $cart->item_count : 0;
    }
}
