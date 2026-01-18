<?php

namespace App\Livewire\Pages\Orders;

use App\Models\Order;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Order detail page component.
 *
 * @see PANTALLAS.md §A6 - Confirmación de pago
 * @see PANTALLAS.md §A8 - Mis compras (detalle orden)
 */
class Show extends Component
{
    #[Locked]
    public Order $order;

    public function mount(Order $order): void
    {
        // Ensure user can view this order
        if (auth()->check()) {
            // Logged in user can only see their own orders
            if ($order->user_id && $order->user_id !== auth()->id()) {
                abort(403);
            }
        } else {
            // Guest can only see orders without user_id (their own guest orders)
            // In future, implement order lookup by support code
            if ($order->user_id) {
                abort(403);
            }
        }

        $this->order = $order->load([
            'items.raffle',
            'items.package',
            'transactions',
            'tickets.raffle',
            'events' => fn ($q) => $q->orderBy('created_at', 'desc'),
        ]);
    }

    public function render(): View
    {
        return view('livewire.pages.orders.show')
            ->layout('layouts.public', ['title' => 'Orden #'.$this->order->order_number]);
    }
}
