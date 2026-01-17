<div>
    {{-- Header --}}
    <div class="mb-8">
        <h1 class="text-[#111811] dark:text-white tracking-tight text-[32px] font-bold leading-tight">Pagar Orden</h1>
        <p class="text-[#618961] dark:text-white/60 text-base">
            Orden #{{ $this->order->order_number }} - Total: {{ $this->order->formatted_total }}
        </p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Payment Section --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Order Summary --}}
            <div class="bg-white dark:bg-white/5 rounded-xl border border-[#dbe6db] dark:border-white/10 p-6">
                <h2 class="text-[#111811] dark:text-white font-bold text-xl mb-6 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#13ec13]">receipt_long</span>
                    Resumen de la orden
                </h2>

                <div class="space-y-4">
                    @foreach($this->order->items as $item)
                        <div class="flex items-center gap-4 pb-4 border-b border-[#dbe6db] dark:border-white/10 last:border-0 last:pb-0">
                            <div class="flex-1 min-w-0">
                                <h3 class="text-[#111811] dark:text-white font-semibold">{{ $item->raffle_title }}</h3>
                                <p class="text-[#618961] dark:text-white/60 text-sm">
                                    {{ $item->quantity }} boletos
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-[#111811] dark:text-white font-bold">{{ $item->formatted_subtotal }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Payment Methods --}}
            @if(!$paymentIntent)
                <div class="bg-white dark:bg-white/5 rounded-xl border border-[#dbe6db] dark:border-white/10 p-6">
                    <h2 class="text-[#111811] dark:text-white font-bold text-xl mb-6 flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#13ec13]">credit_card</span>
                        Selecciona tu medio de pago
                    </h2>

                    @if($this->availableGateways->isEmpty())
                        <div class="text-center py-8">
                            <span class="material-symbols-outlined text-4xl text-gray-300 dark:text-white/20 mb-2">credit_card_off</span>
                            <p class="text-[#618961] dark:text-white/60">No hay medios de pago disponibles en este momento.</p>
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach($this->availableGateways as $gateway)
                                <button
                                    type="button"
                                    wire:click="selectProvider('{{ $gateway->provider->value }}')"
                                    class="w-full p-4 rounded-lg border-2 transition-all flex items-center gap-4 {{ $selectedProvider === $gateway->provider->value ? 'border-[#13ec13] bg-[#13ec13]/5' : 'border-[#dbe6db] dark:border-white/10 hover:border-[#13ec13]/50' }}"
                                >
                                    @if($gateway->logo_url)
                                        <img src="{{ $gateway->logo_url }}" alt="{{ $gateway->display_name }}" class="h-8 w-auto">
                                    @else
                                        <div class="h-10 w-10 rounded-lg bg-[#f0f4f0] dark:bg-white/10 flex items-center justify-center">
                                            <span class="material-symbols-outlined text-[#618961]">credit_card</span>
                                        </div>
                                    @endif
                                    <div class="text-left flex-1">
                                        <p class="text-[#111811] dark:text-white font-semibold">{{ $gateway->display_name }}</p>
                                        @if($gateway->description)
                                            <p class="text-[#618961] dark:text-white/60 text-sm">{{ $gateway->description }}</p>
                                        @endif
                                    </div>
                                    @if($selectedProvider === $gateway->provider->value)
                                        <span class="material-symbols-outlined text-[#13ec13]">check_circle</span>
                                    @endif
                                </button>
                            @endforeach
                        </div>

                        @if($selectedProvider)
                            <div class="mt-6">
                                <button
                                    type="button"
                                    wire:click="initiatePayment"
                                    wire:loading.attr="disabled"
                                    wire:target="initiatePayment"
                                    class="w-full py-4 bg-[#13ec13] hover:bg-[#13ec13]/90 disabled:bg-gray-300 disabled:cursor-not-allowed text-white font-bold rounded-lg transition-colors flex items-center justify-center gap-2"
                                >
                                    <span wire:loading.remove wire:target="initiatePayment" class="flex items-center gap-2">
                                        <span class="material-symbols-outlined">lock</span>
                                        Continuar con el pago
                                    </span>
                                    <span wire:loading wire:target="initiatePayment" class="flex items-center gap-2">
                                        <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Preparando pago...
                                    </span>
                                </button>
                            </div>
                        @endif
                    @endif
                </div>
            @else
                {{-- Wompi Widget --}}
                @if($selectedProvider === 'wompi')
                    <div class="bg-white dark:bg-white/5 rounded-xl border border-[#dbe6db] dark:border-white/10 p-6">
                        <h2 class="text-[#111811] dark:text-white font-bold text-xl mb-6 flex items-center gap-2">
                            <span class="material-symbols-outlined text-[#13ec13]">credit_card</span>
                            Completa tu pago con Wompi
                        </h2>

                        <div id="wompi-checkout-container" class="min-h-[400px] flex items-center justify-center">
                            <div class="text-center">
                                <div class="animate-spin h-8 w-8 border-4 border-[#13ec13] border-t-transparent rounded-full mx-auto mb-4"></div>
                                <p class="text-[#618961]">Cargando pasarela de pago...</p>
                            </div>
                        </div>

                        {{-- Wompi Widget Script --}}
                        <script src="{{ $paymentIntent->widgetUrl }}"></script>
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                const container = document.getElementById('wompi-checkout-container');
                                container.innerHTML = '';

                                const checkout = new WidgetCheckout({
                                    currency: '{{ $paymentIntent->currency }}',
                                    amountInCents: {{ $paymentIntent->amountInCents }},
                                    reference: '{{ $paymentIntent->reference }}',
                                    publicKey: '{{ $paymentIntent->publicKey }}',
                                    signature: {
                                        integrity: '{{ $paymentIntent->signature }}'
                                    },
                                    redirectUrl: '{{ $paymentIntent->redirectUrl }}',
                                    customerData: {
                                        email: '{{ $paymentIntent->extra['customer_email'] ?? '' }}',
                                        fullName: '{{ $paymentIntent->extra['customer_name'] ?? '' }}'
                                    }
                                });

                                checkout.open(function(result) {
                                    const transaction = result.transaction;
                                    console.log('Wompi transaction result:', transaction);

                                    if (transaction.status === 'APPROVED') {
                                        window.location.href = '{{ $paymentIntent->redirectUrl }}' + '?id=' + transaction.id;
                                    } else if (transaction.status === 'DECLINED' || transaction.status === 'VOIDED' || transaction.status === 'ERROR') {
                                        window.location.href = '{{ $paymentIntent->redirectUrl }}' + '?id=' + transaction.id + '&status=' + transaction.status;
                                    }
                                    // For PENDING, let the webhook handle it
                                });
                            });
                        </script>
                    </div>
                @endif

                <div class="mt-4">
                    <button
                        type="button"
                        wire:click="$set('paymentIntent', null)"
                        class="text-[#618961] hover:text-[#13ec13] text-sm flex items-center gap-1"
                    >
                        <span class="material-symbols-outlined text-lg">arrow_back</span>
                        Cambiar medio de pago
                    </button>
                </div>
            @endif
        </div>

        {{-- Order Details Sidebar --}}
        <div class="lg:col-span-1">
            <div class="bg-white dark:bg-white/5 rounded-xl border border-[#dbe6db] dark:border-white/10 p-6 sticky top-24">
                <h2 class="text-[#111811] dark:text-white font-bold text-xl mb-6">Detalles del pedido</h2>

                <div class="space-y-3 mb-6 text-sm">
                    <div class="flex justify-between">
                        <span class="text-[#618961] dark:text-white/60">Orden</span>
                        <span class="text-[#111811] dark:text-white font-medium">#{{ $this->order->order_number }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[#618961] dark:text-white/60">Cliente</span>
                        <span class="text-[#111811] dark:text-white font-medium">{{ $this->order->customer_name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[#618961] dark:text-white/60">Email</span>
                        <span class="text-[#111811] dark:text-white font-medium text-xs">{{ $this->order->customer_email }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[#618961] dark:text-white/60">Boletos</span>
                        <span class="text-[#111811] dark:text-white font-medium">{{ $this->order->total_tickets }}</span>
                    </div>
                </div>

                <div class="border-t border-[#dbe6db] dark:border-white/10 pt-4 mb-6">
                    <div class="flex justify-between items-center">
                        <span class="text-[#111811] dark:text-white font-bold text-lg">Total</span>
                        <span class="text-[#13ec13] font-bold text-3xl">{{ $this->order->formatted_total }}</span>
                    </div>
                </div>

                {{-- Support code --}}
                <div class="p-4 bg-[#f0f4f0] dark:bg-white/5 rounded-lg">
                    <p class="text-[#618961] dark:text-white/60 text-xs mb-1">Código de soporte</p>
                    <p class="text-[#111811] dark:text-white font-mono font-bold">{{ $this->order->support_code }}</p>
                </div>

                {{-- Security badges --}}
                <div class="mt-6 pt-6 border-t border-[#dbe6db] dark:border-white/10">
                    <div class="flex items-center justify-center gap-4 text-[#618961] dark:text-white/40">
                        <div class="flex items-center gap-1 text-xs">
                            <span class="material-symbols-outlined text-sm">verified_user</span>
                            Pago Seguro
                        </div>
                        <div class="flex items-center gap-1 text-xs">
                            <span class="material-symbols-outlined text-sm">lock</span>
                            SSL Encriptado
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
