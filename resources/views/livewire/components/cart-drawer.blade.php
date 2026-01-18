<div>
    {{-- Cart Button (Trigger) --}}
    <button
        type="button"
        class="relative p-2 rounded-full hover:bg-gray-100 dark:hover:bg-white/10 transition-colors"
        x-data
        @click="$flux.modal('cart-drawer').show()"
    >
        <span class="material-symbols-outlined text-[24px]">shopping_cart</span>
        @if($this->count > 0)
            <span class="absolute top-1 right-1 bg-[#13ec13] text-[10px] font-bold px-1.5 py-0.5 rounded-full text-black min-w-[18px] text-center">
                {{ $this->count }}
            </span>
        @endif
    </button>

    {{-- Cart Drawer (Flyout Modal) --}}
    <flux:modal name="cart-drawer" flyout position="right" class="w-full max-w-md">
        <div class="flex flex-col h-full">
            {{-- Header --}}
            <div class="flex items-center justify-between pb-4 border-b border-gray-200 dark:border-white/10">
                <flux:heading size="lg">Tu Carrito</flux:heading>
                @if($cart && $cart->items->count() > 0)
                    <span class="text-sm text-gray-500 dark:text-white/60">
                        {{ $this->count }} {{ $this->count === 1 ? 'boleto' : 'boletos' }}
                    </span>
                @endif
            </div>

            {{-- Content --}}
            @if($cart && $cart->items->count() > 0)
                {{-- Items List --}}
                <div class="flex-1 overflow-y-auto py-4 space-y-4">
                    @foreach($cart->items as $item)
                        <div wire:key="cart-item-{{ $item->id }}" class="flex gap-4 p-3 bg-gray-50 dark:bg-white/5 rounded-lg">
                            {{-- Image --}}
                            <div class="shrink-0 w-16 h-16 rounded-lg overflow-hidden bg-gray-200 dark:bg-white/10">
                                @if($item->raffle->primaryImage)
                                    <img
                                        src="{{ $item->raffle->primaryImage->url }}"
                                        alt="{{ $item->raffle->title }}"
                                        class="w-full h-full object-cover"
                                    >
                                @else
                                    <div class="w-full h-full flex items-center justify-center">
                                        <span class="material-symbols-outlined text-gray-400 dark:text-white/40">confirmation_number</span>
                                    </div>
                                @endif
                            </div>

                            {{-- Info --}}
                            <div class="flex-1 min-w-0">
                                <h4 class="font-medium text-sm text-gray-900 dark:text-white truncate">
                                    {{ $item->raffle->title }}
                                </h4>
                                <p class="text-xs text-gray-500 dark:text-white/60 mt-0.5">
                                    {{ $item->formatted_unit_price }} c/u
                                </p>

                                {{-- Quantity Controls --}}
                                <div class="flex items-center gap-2 mt-2">
                                    <button
                                        type="button"
                                        wire:click="decrementItem({{ $item->id }})"
                                        wire:loading.attr="disabled"
                                        class="w-7 h-7 flex items-center justify-center rounded-full bg-gray-200 dark:bg-white/10 hover:bg-gray-300 dark:hover:bg-white/20 transition-colors disabled:opacity-50"
                                    >
                                        <span class="material-symbols-outlined text-[16px]">remove</span>
                                    </button>
                                    <span class="w-8 text-center text-sm font-medium">{{ $item->quantity }}</span>
                                    <button
                                        type="button"
                                        wire:click="incrementItem({{ $item->id }})"
                                        wire:loading.attr="disabled"
                                        class="w-7 h-7 flex items-center justify-center rounded-full bg-gray-200 dark:bg-white/10 hover:bg-gray-300 dark:hover:bg-white/20 transition-colors disabled:opacity-50"
                                    >
                                        <span class="material-symbols-outlined text-[16px]">add</span>
                                    </button>
                                </div>
                            </div>

                            {{-- Subtotal & Remove --}}
                            <div class="flex flex-col items-end justify-between">
                                <span class="font-semibold text-sm text-[#13ec13]">
                                    {{ $item->formatted_subtotal }}
                                </span>
                                <button
                                    type="button"
                                    wire:click="removeItem({{ $item->id }})"
                                    wire:loading.attr="disabled"
                                    class="text-gray-400 hover:text-red-500 dark:text-white/40 dark:hover:text-red-400 transition-colors"
                                    title="Eliminar"
                                >
                                    <span class="material-symbols-outlined text-[18px]">delete</span>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Footer --}}
                <div class="pt-4 border-t border-gray-200 dark:border-white/10 space-y-4">
                    {{-- Total --}}
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600 dark:text-white/70 font-medium">Total</span>
                        <span class="text-xl font-bold text-[#13ec13]">{{ $cart->formatted_total }}</span>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="flex flex-col gap-2">
                        <a
                            href="{{ route('checkout') }}"
                            wire:navigate
                            class="w-full bg-[#13ec13] text-black px-6 py-3 rounded-lg text-sm font-bold tracking-wide hover:opacity-90 transition-all text-center"
                        >
                            Ir al checkout
                        </a>
                        <a
                            href="{{ route('cart') }}"
                            wire:navigate
                            class="w-full bg-gray-100 dark:bg-white/10 text-gray-700 dark:text-white px-6 py-3 rounded-lg text-sm font-medium hover:bg-gray-200 dark:hover:bg-white/20 transition-all text-center"
                        >
                            Ver carrito completo
                        </a>
                    </div>
                </div>
            @else
                {{-- Empty State --}}
                <div class="flex-1 flex flex-col items-center justify-center py-12 text-center">
                    <div class="w-20 h-20 rounded-full bg-gray-100 dark:bg-white/5 flex items-center justify-center mb-4">
                        <span class="material-symbols-outlined text-[40px] text-gray-400 dark:text-white/30">shopping_cart</span>
                    </div>
                    <p class="text-gray-500 dark:text-white/60">Tu carrito está vacío</p>
                </div>
            @endif
        </div>
    </flux:modal>
</div>
