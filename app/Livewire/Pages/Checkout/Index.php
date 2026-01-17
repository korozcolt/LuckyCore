<?php

namespace App\Livewire\Pages\Checkout;

use App\Models\Cart;
use App\Models\Order;
use App\Services\CartService;
use App\Services\CheckoutService;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Livewire\Component;

/**
 * Checkout page component.
 *
 * @see PANTALLAS.md §A5 - Checkout
 */
class Index extends Component
{
    #[Validate('required|string|min:2|max:255')]
    public string $customerName = '';

    #[Validate('required|email|max:255')]
    public string $customerEmail = '';

    #[Validate('nullable|string|max:20')]
    public ?string $customerPhone = null;

    #[Validate('accepted')]
    public bool $termsAccepted = false;

    public array $validationErrors = [];
    public bool $processing = false;

    public function mount(CartService $cartService): void
    {
        // Redirect to cart if empty
        $cart = $this->cart;
        if (! $cart || $cart->isEmpty()) {
            $this->redirect(route('cart'), navigate: true);
            return;
        }

        // Pre-fill with user data if authenticated
        if (auth()->check()) {
            $user = auth()->user();
            $this->customerName = $user->name;
            $this->customerEmail = $user->email;
        }

        // Validate cart items
        $this->validationErrors = $cartService->validateCart($cart);
        if (! empty($this->validationErrors)) {
            session()->flash('error', 'Hay errores en tu carrito que debes corregir.');
            $this->redirect(route('cart'), navigate: true);
        }
    }

    #[Computed]
    public function cart(): ?Cart
    {
        return app(CartService::class)->findActiveCart(
            auth()->user(),
            session()->getId()
        );
    }

    public function placeOrder(CheckoutService $checkoutService): void
    {
        $this->validate();

        if ($this->processing) {
            return;
        }

        $this->processing = true;

        try {
            $cart = $this->cart;
            if (! $cart || $cart->isEmpty()) {
                throw new \InvalidArgumentException('El carrito está vacío.');
            }

            $order = $checkoutService->createOrder(
                cart: $cart,
                user: auth()->user(),
                customerData: [
                    'name' => $this->customerName,
                    'email' => $this->customerEmail,
                    'phone' => $this->customerPhone,
                ],
                termsAccepted: $this->termsAccepted
            );

            // Dispatch event for cart update in header
            $this->dispatch('cart-updated');

            // Redirect to payment page (placeholder for Sprint 3)
            session()->flash('success', 'Orden creada exitosamente. Código de soporte: ' . $order->support_code);
            $this->redirect(route('orders.show', $order), navigate: true);

        } catch (\InvalidArgumentException $e) {
            $this->processing = false;
            $this->dispatch('notify', message: $e->getMessage(), type: 'error');
        } catch (\Exception $e) {
            $this->processing = false;
            report($e);
            $this->dispatch('notify', message: 'Ocurrió un error al procesar tu orden. Por favor intenta de nuevo.', type: 'error');
        }
    }

    public function render(): View
    {
        return view('livewire.pages.checkout.index')
            ->layout('layouts.public', ['title' => 'Checkout']);
    }
}
