<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderEvent;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Service for handling checkout and order creation.
 *
 * @see PANTALLAS.md §A5 - Checkout
 */
class CheckoutService
{
    public function __construct(
        protected CartService $cartService
    ) {}

    /**
     * Create an order from a cart.
     *
     * @throws \InvalidArgumentException
     */
    public function createOrder(
        Cart $cart,
        ?User $user,
        array $customerData,
        bool $termsAccepted
    ): Order {
        // Validate cart
        $errors = $this->cartService->validateCart($cart);
        if (! empty($errors)) {
            throw new \InvalidArgumentException(
                'El carrito tiene errores que deben ser corregidos: ' . implode(', ', $errors)
            );
        }

        if ($cart->isEmpty()) {
            throw new \InvalidArgumentException('El carrito está vacío.');
        }

        if (! $termsAccepted) {
            throw new \InvalidArgumentException('Debes aceptar los términos y condiciones.');
        }

        // Validate customer data
        $this->validateCustomerData($customerData);

        return DB::transaction(function () use ($cart, $user, $customerData, $termsAccepted) {
            // Calculate totals
            $cart->load('items.raffle');
            $subtotal = $cart->total;
            $total = $subtotal; // No fees or taxes for now

            // Create order
            $order = Order::create([
                'user_id' => $user?->id,
                'cart_id' => $cart->id,
                'subtotal' => $subtotal,
                'total' => $total,
                'status' => OrderStatus::Pending,
                'customer_email' => $customerData['email'],
                'customer_name' => $customerData['name'],
                'customer_phone' => $customerData['phone'] ?? null,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'terms_accepted' => $termsAccepted,
                'terms_accepted_at' => now(),
            ]);

            // Create order items from cart items
            foreach ($cart->items as $cartItem) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'raffle_id' => $cartItem->raffle_id,
                    'raffle_package_id' => $cartItem->raffle_package_id,
                    'quantity' => $cartItem->quantity,
                    'unit_price' => $cartItem->unit_price,
                    'subtotal' => $cartItem->subtotal,
                    'raffle_title' => $cartItem->raffle->title,
                    'tickets_assigned' => 0,
                    'tickets_complete' => false,
                ]);
            }

            // Mark cart as converted
            $cart->update(['converted_at' => now()]);

            // Log order created event
            OrderEvent::log(
                order: $order,
                eventType: OrderEvent::ORDER_CREATED,
                description: 'Orden creada desde carrito',
                metadata: [
                    'items_count' => $cart->items->count(),
                    'total_tickets' => $cart->item_count,
                    'ip_address' => request()->ip(),
                ]
            );

            return $order->load('items.raffle');
        });
    }

    /**
     * Validate customer data.
     *
     * @throws \InvalidArgumentException
     */
    protected function validateCustomerData(array $data): void
    {
        if (empty($data['email']) || ! filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('El correo electrónico es inválido.');
        }

        if (empty($data['name']) || strlen($data['name']) < 2) {
            throw new \InvalidArgumentException('El nombre es requerido.');
        }
    }

    /**
     * Get order by support code for customer lookup.
     */
    public function findBySuportCode(string $supportCode): ?Order
    {
        return Order::where('support_code', $supportCode)
            ->with(['items.raffle', 'transactions'])
            ->first();
    }

    /**
     * Get order by ID for the user.
     */
    public function findForUser(int $orderId, User $user): ?Order
    {
        return Order::where('id', $orderId)
            ->where('user_id', $user->id)
            ->with(['items.raffle', 'transactions', 'events'])
            ->first();
    }
}
