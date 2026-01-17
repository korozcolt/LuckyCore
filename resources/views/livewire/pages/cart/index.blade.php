<div>
    {{-- Header --}}
    <div class="mb-8">
        <h1 class="text-[#111811] dark:text-white tracking-tight text-[32px] font-bold leading-tight">Carrito de Compras</h1>
        <p class="text-[#618961] dark:text-white/60 text-base">Revisa tu selección antes de continuar al pago.</p>
    </div>

    @if($this->cart && $this->cart->items->count() > 0)
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Cart Items --}}
            <div class="lg:col-span-2 space-y-4">
                @foreach($this->cart->items as $item)
                    @php
                        $hasError = isset($validationErrors[$item->raffle->slug]);
                    @endphp
                    <div class="bg-white dark:bg-white/5 rounded-xl border {{ $hasError ? 'border-red-500' : 'border-[#dbe6db] dark:border-white/10' }} overflow-hidden">
                        <div class="flex flex-col sm:flex-row">
                            {{-- Image --}}
                            <div class="w-full sm:w-40 h-32 sm:h-auto flex-shrink-0">
                                <div class="w-full h-full bg-center bg-cover" style="background-image: url('{{ $item->raffle->primaryImage?->url ?? 'https://placehold.co/160x120/1a2e1a/13ec13?text=' . urlencode($item->raffle->title) }}');"></div>
                            </div>

                            {{-- Content --}}
                            <div class="flex-1 p-5">
                                <div class="flex justify-between items-start mb-3">
                                    <div>
                                        <a href="{{ route('raffles.show', $item->raffle) }}" class="text-[#111811] dark:text-white font-bold text-lg hover:text-[#13ec13] transition-colors" wire:navigate>
                                            {{ $item->raffle->title }}
                                        </a>
                                        @if($item->package)
                                            <span class="ml-2 px-2 py-0.5 bg-[#13ec13]/10 text-[#13ec13] text-xs font-semibold rounded-full">
                                                {{ $item->package->name }}
                                            </span>
                                        @endif
                                    </div>
                                    <button
                                        wire:click="removeItem({{ $item->id }})"
                                        wire:confirm="¿Estás seguro de eliminar este producto?"
                                        class="text-gray-400 hover:text-red-500 transition-colors p-1"
                                    >
                                        <span class="material-symbols-outlined text-xl">delete</span>
                                    </button>
                                </div>

                                {{-- Error Message --}}
                                @if($hasError)
                                    <div class="mb-3 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                                        <p class="text-red-600 dark:text-red-400 text-sm flex items-center gap-2">
                                            <span class="material-symbols-outlined text-base">error</span>
                                            {{ $validationErrors[$item->raffle->slug] }}
                                        </p>
                                    </div>
                                @endif

                                {{-- Price and Quantity --}}
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                    <div class="flex items-center gap-2">
                                        <span class="text-[#618961] dark:text-white/60 text-sm">Precio unitario:</span>
                                        <span class="text-[#111811] dark:text-white font-semibold">{{ $item->formatted_unit_price }}</span>
                                    </div>

                                    {{-- Quantity Controls --}}
                                    <div class="flex items-center gap-3">
                                        <span class="text-[#618961] dark:text-white/60 text-sm">Cantidad:</span>
                                        <div class="flex items-center gap-1 bg-[#f0f4f0] dark:bg-white/10 rounded-lg p-1">
                                            <button
                                                wire:click="decrementQuantity({{ $item->id }})"
                                                class="w-8 h-8 flex items-center justify-center rounded-md hover:bg-white dark:hover:bg-white/10 transition-colors {{ $item->quantity <= $item->raffle->min_purchase_qty ? 'opacity-50 cursor-not-allowed' : '' }}"
                                                {{ $item->quantity <= $item->raffle->min_purchase_qty ? 'disabled' : '' }}
                                            >
                                                <span class="material-symbols-outlined text-lg">remove</span>
                                            </button>
                                            <span class="w-12 text-center font-bold text-[#111811] dark:text-white">{{ $item->quantity }}</span>
                                            <button
                                                wire:click="incrementQuantity({{ $item->id }})"
                                                class="w-8 h-8 flex items-center justify-center rounded-md hover:bg-white dark:hover:bg-white/10 transition-colors"
                                            >
                                                <span class="material-symbols-outlined text-lg">add</span>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="text-right">
                                        <span class="text-[#618961] dark:text-white/60 text-sm">Subtotal:</span>
                                        <p class="text-[#13ec13] font-bold text-xl">{{ $item->formatted_subtotal }}</p>
                                    </div>
                                </div>

                                {{-- Package Options --}}
                                @if($item->raffle->packages->count() > 0)
                                    <div class="mt-4 pt-4 border-t border-[#dbe6db] dark:border-white/10">
                                        <p class="text-[#618961] dark:text-white/60 text-sm mb-2">Cambiar a paquete:</p>
                                        <div class="flex flex-wrap gap-2">
                                            @foreach($item->raffle->packages as $package)
                                                <button
                                                    wire:click="selectPackage({{ $item->id }}, {{ $package->id }})"
                                                    class="px-3 py-1.5 rounded-lg text-sm font-semibold transition-colors {{ $item->raffle_package_id === $package->id ? 'bg-[#13ec13] text-white' : 'bg-[#f0f4f0] dark:bg-white/10 text-[#618961] dark:text-white/60 hover:bg-[#13ec13]/10' }}"
                                                >
                                                    {{ $package->quantity }} boletos
                                                    @if($package->is_recommended)
                                                        <span class="material-symbols-outlined text-xs align-middle">star</span>
                                                    @endif
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Order Summary --}}
            <div class="lg:col-span-1">
                <div class="bg-white dark:bg-white/5 rounded-xl border border-[#dbe6db] dark:border-white/10 p-6 sticky top-24">
                    <h2 class="text-[#111811] dark:text-white font-bold text-xl mb-6">Resumen del Pedido</h2>

                    <div class="space-y-3 mb-6">
                        @foreach($this->cart->items as $item)
                            <div class="flex justify-between text-sm">
                                <span class="text-[#618961] dark:text-white/60">{{ $item->raffle->title }} (x{{ $item->quantity }})</span>
                                <span class="text-[#111811] dark:text-white font-medium">{{ $item->formatted_subtotal }}</span>
                            </div>
                        @endforeach
                    </div>

                    <div class="border-t border-[#dbe6db] dark:border-white/10 pt-4 mb-6">
                        <div class="flex justify-between items-center">
                            <span class="text-[#111811] dark:text-white font-bold text-lg">Total</span>
                            <span class="text-[#13ec13] font-bold text-2xl">{{ $this->cart->formatted_total }}</span>
                        </div>
                        <p class="text-[#618961] dark:text-white/60 text-sm mt-1">
                            {{ $this->cart->item_count }} boletos en total
                        </p>
                    </div>

                    @if(!empty($validationErrors))
                        <div class="mb-4 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                            <p class="text-red-600 dark:text-red-400 text-sm flex items-center gap-2">
                                <span class="material-symbols-outlined text-base">warning</span>
                                Hay errores que debes corregir antes de continuar
                            </p>
                        </div>
                    @endif

                    <button
                        wire:click="proceedToCheckout"
                        class="w-full py-4 bg-[#13ec13] hover:bg-[#13ec13]/90 disabled:bg-gray-300 disabled:cursor-not-allowed text-white font-bold rounded-lg transition-colors flex items-center justify-center gap-2"
                        {{ !empty($validationErrors) ? 'disabled' : '' }}
                    >
                        <span class="material-symbols-outlined">shopping_cart_checkout</span>
                        Continuar al Pago
                    </button>

                    <a href="{{ route('raffles.index') }}" class="w-full mt-3 py-3 border border-[#dbe6db] dark:border-white/10 text-[#618961] dark:text-white/60 font-semibold rounded-lg hover:bg-[#f0f4f0] dark:hover:bg-white/5 transition-colors flex items-center justify-center gap-2" wire:navigate>
                        <span class="material-symbols-outlined text-lg">arrow_back</span>
                        Seguir Comprando
                    </a>
                </div>
            </div>
        </div>
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
