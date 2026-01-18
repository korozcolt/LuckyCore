<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Services\TicketAssignmentService;
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
    public function callback(
        Request $request,
        string $provider,
        Order $order,
        TicketAssignmentService $ticketAssignmentService,
    ): RedirectResponse {
        // The webhook will handle the actual payment status update
        // This just redirects the user to the order confirmation page with appropriate message

        // Refresh order to get latest status (webhook may have updated it)
        $order->refresh();

        $status = $request->query('status');
        $transactionId = $request->query('id');

        // Determine message based on status
        if ($order->status === OrderStatus::Paid) {
            if (! $order->allTicketsAssigned()) {
                $ticketAssignmentService->assignForOrder($order);
                $order->refresh();
            }

            session()->flash('success', '¡Pago exitoso! Tu compra ha sido confirmada.');
        } elseif ($status === 'DECLINED' || $status === 'ERROR' || $status === 'VOIDED') {
            session()->flash('error', 'El pago fue rechazado. Puedes intentar de nuevo.');

            // Redirect back to payment page to retry
            return redirect()->route('payment', $order->ulid);
        } else {
            // Pending or unknown status - webhook will update
            session()->flash('info', 'Tu pago está siendo procesado. Te notificaremos cuando sea confirmado.');
        }

        return redirect()->route('orders.confirmation', $order->ulid);
    }
}
