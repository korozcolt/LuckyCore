<?php

declare(strict_types=1);

namespace App\Livewire\Pages\Payment;

use App\Enums\PaymentProvider;
use App\Models\Order;
use App\Models\OrderEvent;
use App\Payments\PaymentManager;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Payment page component - handles payment gateway selection and checkout.
 *
 * @see PANTALLAS.md §A5 - Checkout
 */
class Index extends Component
{
    #[Locked]
    public int $orderId;

    public ?string $selectedProvider = null;

    public bool $processing = false;

    public ?array $paymentIntent = null;

    public function mount(Order $order, PaymentManager $paymentManager): void
    {
        // Check if order can be paid
        if ($order->isPaid()) {
            session()->flash('info', 'Esta orden ya ha sido pagada.');
            $this->redirect(route('orders.show', $order), navigate: true);

            return;
        }

        if (! $order->canPay()) {
            session()->flash('error', 'Esta orden no puede ser procesada.');
            $this->redirect(route('orders.show', $order), navigate: true);

            return;
        }

        // Verify ownership if user is authenticated
        if (auth()->check() && $order->user_id && $order->user_id !== auth()->id()) {
            abort(403);
        }

        $this->orderId = $order->id;

        // Pre-select the only provider if there's just one
        $gateways = $paymentManager->activeGateways();
        if ($gateways->count() === 1) {
            $this->selectedProvider = $gateways->first()->provider->value;
        }
    }

    #[Computed]
    public function order(): Order
    {
        return Order::with(['items.raffle', 'transactions'])->findOrFail($this->orderId);
    }

    #[Computed]
    public function availableGateways(): \Illuminate\Support\Collection
    {
        return app(PaymentManager::class)->activeGateways();
    }

    public function selectProvider(string $provider): void
    {
        if (PaymentProvider::tryFrom($provider)) {
            $this->selectedProvider = $provider;
            $this->paymentIntent = null;
        }
    }

    public function initiatePayment(PaymentManager $paymentManager): void
    {
        if ($this->processing || ! $this->selectedProvider) {
            return;
        }

        $this->processing = true;

        try {
            $provider = PaymentProvider::from($this->selectedProvider);
            $paymentProvider = $paymentManager->provider($provider);

            // Create payment intent
            $intentData = $paymentProvider->createPaymentIntent($this->order);

            // Log payment initiated event
            OrderEvent::log(
                order: $this->order,
                eventType: OrderEvent::PAYMENT_INTENT_CREATED,
                description: "Pago iniciado con {$provider->name}",
                metadata: [
                    'provider' => $provider->value,
                    'transaction_id' => $intentData->transaction->id,
                    'amount' => $intentData->amountInCents,
                ]
            );

            // Store as array for Livewire compatibility
            $this->paymentIntent = $intentData->toArray();

            // Dispatch event to initialize payment widget in browser
            if ($provider === PaymentProvider::Wompi) {
                $this->dispatch('init-wompi-widget', config: $this->paymentIntent);
            }

            $this->processing = false;
        } catch (\Exception $e) {
            $this->processing = false;
            report($e);
            $this->dispatch('notify', message: 'Error al iniciar el pago. Por favor intenta de nuevo.', type: 'error');
        }
    }

    public function render(): View
    {
        return view('livewire.pages.payment.index')
            ->layout('layouts.public', ['title' => 'Pagar Orden']);
    }
}
