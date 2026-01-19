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
                                    {{-- Provider Logo --}}
                                    <div class="w-24 h-10 flex items-center justify-start shrink-0">
                                        @switch($gateway->provider->value)
                                            @case('wompi')
                                                <img src="{{ asset('images/wompi_logo_fondo_claro.png') }}" alt="Wompi" class="h-8 w-auto dark:hidden">
                                                <img src="{{ asset('images/wompi_logo_fondo_oscuro.png') }}" alt="Wompi" class="h-8 w-auto hidden dark:block">
                                                @break
                                            @case('mercadopago')
                                                <img src="{{ asset('images/mercado_pago_logo_horizontal_fondo_claro.png') }}" alt="MercadoPago" class="h-7 w-auto dark:hidden">
                                                <img src="{{ asset('images/mercado_pago_logo_horizontal_fondo_oscuro.svg') }}" alt="MercadoPago" class="h-7 w-auto hidden dark:block">
                                                @break
                                            @case('epayco')
                                                <img src="{{ asset('images/epayco-logo-fondo-claro.png') }}" alt="ePayco" class="h-8 w-auto dark:hidden">
                                                <img src="{{ asset('images/epayco-logo-fondo-oscuro.png') }}" alt="ePayco" class="h-8 w-auto hidden dark:block">
                                                @break
                                            @default
                                                @if($gateway->logo_url)
                                                    <img src="{{ $gateway->logo_url }}" alt="{{ $gateway->display_name }}" class="h-8 w-auto">
                                                @else
                                                    <div class="h-10 w-10 rounded-lg bg-[#f0f4f0] dark:bg-white/10 flex items-center justify-center">
                                                        <span class="material-symbols-outlined text-[#618961]">credit_card</span>
                                                    </div>
                                                @endif
                                        @endswitch
                                    </div>
                                    <div class="text-left flex-1">
                                        @if($gateway->description)
                                            <p class="text-[#618961] dark:text-white/60 text-sm">{{ $gateway->description }}</p>
                                        @else
                                            <p class="text-[#111811] dark:text-white font-semibold">{{ $gateway->display_name }}</p>
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

                        {{-- Wompi Widget Container --}}
                        <div wire:ignore class="min-h-[150px]" id="wompi-container">
                            <div class="text-center py-8" id="wompi-status">
                                <div id="wompi-spinner" class="animate-spin h-8 w-8 border-4 border-[#13ec13] border-t-transparent rounded-full mx-auto mb-4"></div>
                                <p id="wompi-message" class="text-[#618961] dark:text-white/60">Cargando pasarela de pago...</p>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- MercadoPago Widget --}}
                @if($selectedProvider === 'mercadopago')
                    <div class="bg-white dark:bg-white/5 rounded-xl border border-[#dbe6db] dark:border-white/10 p-6">
                        <h2 class="text-[#111811] dark:text-white font-bold text-xl mb-6 flex items-center gap-2">
                            <span class="material-symbols-outlined text-[#13ec13]">credit_card</span>
                            Completa tu pago con MercadoPago
                        </h2>

                        <div id="mercadopago-checkout-container" class="min-h-[200px]">
                            {{-- MercadoPago Checkout Pro Button --}}
                            <div
                                x-data="{
                                    loading: true,
                                    init() {
                                        this.loadMercadoPagoSdk();
                                    },
                                    loadMercadoPagoSdk() {
                                        if (typeof MercadoPago === 'undefined') {
                                            const script = document.createElement('script');
                                            script.src = '{{ $paymentIntent['widget_url'] }}';
                                            script.onload = () => this.initCheckout();
                                            document.head.appendChild(script);
                                        } else {
                                            this.initCheckout();
                                        }
                                    },
                                    initCheckout() {
                                        const mp = new MercadoPago('{{ $paymentIntent['public_key'] }}', {
                                            locale: 'es-CO'
                                        });

                                        mp.checkout({
                                            preference: {
                                                id: '{{ $paymentIntent['extra']['preference_id'] }}'
                                            },
                                            render: {
                                                container: '#mp-checkout-btn',
                                                label: 'Pagar con MercadoPago'
                                            }
                                        });

                                        this.loading = false;
                                    }
                                }"
                            >
                                <div x-show="loading" class="flex items-center justify-center py-8">
                                    <div class="text-center">
                                        <div class="animate-spin h-8 w-8 border-4 border-[#13ec13] border-t-transparent rounded-full mx-auto mb-4"></div>
                                        <p class="text-[#618961] dark:text-white/60">Cargando MercadoPago...</p>
                                    </div>
                                </div>

                                <div x-show="!loading" class="space-y-4">
                                    <div class="text-center py-4">
                                        <p class="text-[#618961] dark:text-white/60 mb-4">
                                            Serás redirigido a MercadoPago para completar tu pago de forma segura.
                                        </p>
                                        <div id="mp-checkout-btn" class="flex justify-center"></div>
                                    </div>

                                    {{-- Alternative direct link --}}
                                    <div class="text-center pt-4 border-t border-[#dbe6db] dark:border-white/10">
                                        <p class="text-[#618961] dark:text-white/60 text-sm mb-3">¿El botón no funciona?</p>
                                        <a
                                            href="{{ $paymentIntent['extra']['init_point'] ?? '#' }}"
                                            class="inline-flex items-center gap-2 px-6 py-3 bg-[#009ee3] hover:bg-[#007cb0] text-white font-semibold rounded-lg transition-colors"
                                        >
                                            <span class="material-symbols-outlined">open_in_new</span>
                                            Ir a MercadoPago
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- ePayco Widget --}}
                @if($selectedProvider === 'epayco')
                    <div class="bg-white dark:bg-white/5 rounded-xl border border-[#dbe6db] dark:border-white/10 p-6">
                        <h2 class="text-[#111811] dark:text-white font-bold text-xl mb-6 flex items-center gap-2">
                            <span class="material-symbols-outlined text-[#13ec13]">credit_card</span>
                            Completa tu pago con ePayco
                        </h2>

                        {{-- ePayco Widget Container --}}
                        <div wire:ignore class="min-h-[150px]" id="epayco-container">
                            <div class="text-center py-8" id="epayco-status">
                                <div id="epayco-spinner" class="animate-spin h-8 w-8 border-4 border-[#13ec13] border-t-transparent rounded-full mx-auto mb-4"></div>
                                <p id="epayco-message" class="text-[#618961] dark:text-white/60">Cargando pasarela de pago...</p>
                            </div>
                        </div>
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

    @script
    <script>
        // Wompi Widget Handler
        $wire.on('init-wompi-widget', ({ config }) => {
            console.log('Wompi widget init event received', config);

            function initWompiWidget() {
                const spinner = document.getElementById('wompi-spinner');
                const message = document.getElementById('wompi-message');

                if (spinner) spinner.style.display = 'none';
                if (message) message.textContent = 'Completa el pago en la ventana de Wompi.';

                const checkout = new WidgetCheckout({
                    currency: config.currency,
                    amountInCents: config.amount_in_cents,
                    reference: config.reference,
                    publicKey: config.public_key,
                    signature: { integrity: config.signature },
                    redirectUrl: config.redirect_url,
                    customerData: {
                        email: config.extra?.customer_email || '',
                        fullName: config.extra?.customer_name || ''
                    }
                });

                checkout.open(function(result) {
                    const transaction = result.transaction;
                    console.log('Wompi result:', transaction);

                    if (transaction.status === 'APPROVED') {
                        window.location.href = config.redirect_url + '?id=' + transaction.id;
                    } else if (['DECLINED', 'VOIDED', 'ERROR'].includes(transaction.status)) {
                        window.location.href = config.redirect_url + '?id=' + transaction.id + '&status=' + transaction.status;
                    }
                });
            }

            if (typeof WidgetCheckout === 'undefined') {
                const script = document.createElement('script');
                script.src = config.widget_url;
                script.onload = initWompiWidget;
                document.head.appendChild(script);
            } else {
                initWompiWidget();
            }
        });

        // ePayco Widget Handler
        $wire.on('init-epayco-widget', ({ config }) => {
            console.log('ePayco widget init event received', config);

            function initEpaycoWidget() {
                const spinner = document.getElementById('epayco-spinner');
                const message = document.getElementById('epayco-message');

                if (spinner) spinner.style.display = 'none';
                if (message) message.textContent = 'Abriendo ventana de pago...';

                // Convert amount from cents to pesos for ePayco
                const amountInPesos = (config.amount_in_cents / 100).toString();

                const handler = ePayco.checkout.configure({
                    key: config.public_key,
                    test: config.extra?.test || false
                });

                const data = {
                    // Required parameters
                    name: config.extra?.name || 'Pago LuckyCore',
                    description: config.extra?.description || 'Compra de boletos',
                    invoice: config.extra?.invoice || config.reference,
                    currency: config.currency.toLowerCase(),
                    amount: amountInPesos,
                    tax_base: config.extra?.tax_base || '0',
                    tax: config.extra?.tax || '0',
                    tax_ico: config.extra?.tax_ico || '0',
                    country: config.extra?.country || 'co',
                    lang: config.extra?.lang || 'es',

                    // Optional customer data
                    name_billing: config.extra?.customer_name || '',
                    email_billing: config.extra?.customer_email || '',

                    // Response URLs
                    external: config.extra?.external || 'false',
                    response: config.extra?.response || config.redirect_url,
                    confirmation: config.extra?.confirmation || '',

                    // Extra fields for tracking
                    extra1: config.extra?.extra1 || '',
                    extra2: config.extra?.extra2 || '',
                    extra3: config.extra?.extra3 || ''
                };

                handler.open(data);
            }

            if (typeof ePayco === 'undefined') {
                const script = document.createElement('script');
                script.src = config.widget_url;
                script.onload = function() {
                    // Small delay to ensure ePayco is fully initialized
                    setTimeout(initEpaycoWidget, 100);
                };
                document.head.appendChild(script);
            } else {
                initEpaycoWidget();
            }
        });
    </script>
    @endscript
</div>
