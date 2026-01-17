<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Payment Callback Controller.
 *
 * Handles redirect callbacks from payment providers after checkout completion.
 *
 * @see PANTALLAS.md §A6 - Confirmación de pago
 */
class PaymentCallbackController extends Controller
{
    /**
     * Handle the payment callback redirect.
     */
    public function callback(Request $request, string $provider, Order $order): RedirectResponse
    {
        // The webhook will handle the actual payment status update
        // This just redirects the user to the order confirmation page

        return redirect()->route('orders.show', $order);
    }
}
