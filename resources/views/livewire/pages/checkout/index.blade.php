<div>
    {{-- Header --}}
    <div class="mb-8">
        <h1 class="text-[#111811] dark:text-white tracking-tight text-[32px] font-bold leading-tight">Checkout</h1>
        <p class="text-[#618961] dark:text-white/60 text-base">Completa tus datos para finalizar la compra.</p>
    </div>

    @if($this->cart && $this->cart->items->count() > 0)
        <form wire:submit="placeOrder" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Customer Information --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Contact Information --}}
                <div class="bg-white dark:bg-white/5 rounded-xl border border-[#dbe6db] dark:border-white/10 p-6">
                    <h2 class="text-[#111811] dark:text-white font-bold text-xl mb-6 flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#13ec13]">person</span>
                        Información de contacto
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Name --}}
                        <div class="md:col-span-2">
                            <label for="customerName" class="block text-sm font-semibold text-[#111811] dark:text-white mb-2">
                                Nombre completo <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                id="customerName"
                                wire:model="customerName"
                                class="w-full px-4 py-3 rounded-lg border border-[#dbe6db] dark:border-white/20 bg-white dark:bg-white/5 text-[#111811] dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-[#13ec13] focus:border-transparent transition-colors"
                                placeholder="Tu nombre completo"
                            >
                            @error('customerName')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Email --}}
                        <div>
                            <label for="customerEmail" class="block text-sm font-semibold text-[#111811] dark:text-white mb-2">
                                Correo electrónico <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="email"
                                id="customerEmail"
                                wire:model="customerEmail"
                                class="w-full px-4 py-3 rounded-lg border border-[#dbe6db] dark:border-white/20 bg-white dark:bg-white/5 text-[#111811] dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-[#13ec13] focus:border-transparent transition-colors"
                                placeholder="tu@email.com"
                            >
                            @error('customerEmail')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Phone --}}
                        <div>
                            <label for="customerPhone" class="block text-sm font-semibold text-[#111811] dark:text-white mb-2">
                                Teléfono <span class="text-[#618961]">(opcional)</span>
                            </label>
                            <input
                                type="tel"
                                id="customerPhone"
                                wire:model="customerPhone"
                                class="w-full px-4 py-3 rounded-lg border border-[#dbe6db] dark:border-white/20 bg-white dark:bg-white/5 text-[#111811] dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-[#13ec13] focus:border-transparent transition-colors"
                                placeholder="300 123 4567"
                            >
                            @error('customerPhone')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Order Items Summary --}}
                <div class="bg-white dark:bg-white/5 rounded-xl border border-[#dbe6db] dark:border-white/10 p-6">
                    <h2 class="text-[#111811] dark:text-white font-bold text-xl mb-6 flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#13ec13]">receipt_long</span>
                        Resumen de tu pedido
                    </h2>

                    <div class="space-y-4">
                        @foreach($this->cart->items as $item)
                            <div class="flex items-center gap-4 pb-4 border-b border-[#dbe6db] dark:border-white/10 last:border-0 last:pb-0">
                                <div class="w-16 h-16 rounded-lg overflow-hidden flex-shrink-0">
                                    <div class="w-full h-full bg-center bg-cover" style="background-image: url('{{ $item->raffle->primaryImage?->url ?? 'https://placehold.co/64x64/1a2e1a/13ec13?text=' . urlencode(substr($item->raffle->title, 0, 2)) }}');"></div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-[#111811] dark:text-white font-semibold truncate">{{ $item->raffle->title }}</h3>
                                    <p class="text-[#618961] dark:text-white/60 text-sm">
                                        {{ $item->quantity }} boletos x {{ $item->formatted_unit_price }}
                                        @if($item->package)
                                            <span class="text-[#13ec13]">({{ $item->package->name }})</span>
                                        @endif
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-[#111811] dark:text-white font-bold">{{ $item->formatted_subtotal }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Terms and Conditions --}}
                <div class="bg-white dark:bg-white/5 rounded-xl border border-[#dbe6db] dark:border-white/10 p-6">
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input
                            type="checkbox"
                            wire:model="termsAccepted"
                            class="mt-1 w-5 h-5 rounded border-gray-300 text-[#13ec13] focus:ring-[#13ec13] focus:ring-offset-0"
                        >
                        <span class="text-[#618961] dark:text-white/60 text-sm">
                            He leído y acepto los
                            <a href="{{ route('page.show', 'terminos-y-condiciones') }}" target="_blank" class="text-[#13ec13] hover:underline">Términos y Condiciones</a>
                            y la
                            <a href="{{ route('page.show', 'politica-de-privacidad') }}" target="_blank" class="text-[#13ec13] hover:underline">Política de Privacidad</a>.
                            <span class="text-red-500">*</span>
                        </span>
                    </label>
                    @error('termsAccepted')
                        <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Order Total & Payment --}}
            <div class="lg:col-span-1">
                <div class="bg-white dark:bg-white/5 rounded-xl border border-[#dbe6db] dark:border-white/10 p-6 sticky top-24">
                    <h2 class="text-[#111811] dark:text-white font-bold text-xl mb-6">Total a pagar</h2>

                    <div class="space-y-3 mb-6">
                        <div class="flex justify-between text-sm">
                            <span class="text-[#618961] dark:text-white/60">Subtotal ({{ $this->cart->item_count }} boletos)</span>
                            <span class="text-[#111811] dark:text-white font-medium">{{ $this->cart->formatted_total }}</span>
                        </div>
                        {{-- Future: Add fees, discounts, etc. --}}
                    </div>

                    <div class="border-t border-[#dbe6db] dark:border-white/10 pt-4 mb-6">
                        <div class="flex justify-between items-center">
                            <span class="text-[#111811] dark:text-white font-bold text-lg">Total</span>
                            <span class="text-[#13ec13] font-bold text-3xl">{{ $this->cart->formatted_total }}</span>
                        </div>
                    </div>

                    {{-- Payment Method (Placeholder for Sprint 3) --}}
                    <div class="mb-6 p-4 bg-[#f0f4f0] dark:bg-white/5 rounded-lg">
                        <p class="text-[#618961] dark:text-white/60 text-sm flex items-center gap-2">
                            <span class="material-symbols-outlined text-lg">info</span>
                            Serás redirigido a la pasarela de pago después de confirmar.
                        </p>
                    </div>

                    <button
                        type="submit"
                        class="w-full py-4 bg-[#13ec13] hover:bg-[#13ec13]/90 disabled:bg-gray-300 disabled:cursor-not-allowed text-white font-bold rounded-lg transition-colors flex items-center justify-center gap-2"
                        wire:loading.attr="disabled"
                        wire:target="placeOrder"
                        {{ $processing ? 'disabled' : '' }}
                    >
                        <span wire:loading.remove wire:target="placeOrder" class="flex items-center gap-2">
                            <span class="material-symbols-outlined">lock</span>
                            Confirmar y Pagar
                        </span>
                        <span wire:loading wire:target="placeOrder" class="flex items-center gap-2">
                            <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Procesando...
                        </span>
                    </button>

                    <a href="{{ route('cart') }}" class="w-full mt-3 py-3 border border-[#dbe6db] dark:border-white/10 text-[#618961] dark:text-white/60 font-semibold rounded-lg hover:bg-[#f0f4f0] dark:hover:bg-white/5 transition-colors flex items-center justify-center gap-2" wire:navigate>
                        <span class="material-symbols-outlined text-lg">arrow_back</span>
                        Volver al Carrito
                    </a>

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
        </form>
    @else
        {{-- Empty Cart --}}
        <div class="rounded-xl border border-[#dbe6db] dark:border-white/10 bg-white dark:bg-white/5 p-12 text-center">
            <span class="material-symbols-outlined text-6xl text-gray-300 dark:text-white/20 mb-4">shopping_cart</span>
            <h2 class="text-[#111811] dark:text-white font-bold text-xl mb-2">Tu carrito está vacío</h2>
            <p class="text-[#618961] dark:text-white/60 mb-6">Agrega sorteos a tu carrito para continuar.</p>
            <a href="{{ route('raffles.index') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-[#13ec13] hover:bg-[#13ec13]/90 text-white font-bold rounded-lg transition-colors" wire:navigate>
                <span class="material-symbols-outlined">confirmation_number</span>
                Ver Sorteos
            </a>
        </div>
    @endif
</div>
