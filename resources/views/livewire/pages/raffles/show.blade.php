<div>
    {{-- Full-Width Hero Header --}}
    <div class="relative w-full h-[350px] lg:h-[450px] overflow-hidden -mx-4 md:-mx-10 -mt-8" style="width: calc(100% + 2rem); margin-left: -1rem; margin-right: -1rem;">
        @php
            $heroImage = $raffle->primaryImage?->url ?? 'https://placehold.co/1200x600/1a2e1a/1a2e1a';
        @endphp
        <div class="absolute inset-0 bg-cover bg-center" style="background-image: linear-gradient(to top, rgba(16, 34, 16, 0.95) 0%, rgba(16, 34, 16, 0.4) 60%, rgba(0, 0, 0, 0) 100%), url('{{ $heroImage }}');"></div>
        <div class="relative max-w-[1280px] mx-auto h-full flex flex-col justify-end px-4 md:px-10 pb-8 lg:pb-12">
            <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6">
                <div class="space-y-3">
                    @php
                        $statusColor = match($raffle->status->value) {
                            'active' => 'bg-[#13ec13]/90 text-black',
                            'upcoming' => 'bg-blue-500 text-white',
                            'completed' => 'bg-gray-600 text-white',
                            default => 'bg-[#13ec13]/90 text-black'
                        };
                    @endphp
                    <span class="{{ $statusColor }} text-xs font-bold uppercase tracking-widest px-3 py-1.5 rounded-full">
                        {{ $raffle->status->getLabel() }}
                    </span>
                    <h1 class="text-white text-3xl md:text-4xl lg:text-5xl font-extrabold leading-tight">{{ $raffle->title }}</h1>
                    @if($raffle->short_description)
                        <p class="text-white/80 text-base lg:text-lg max-w-xl">{{ $raffle->short_description }}</p>
                    @endif
                </div>

                {{-- Timer Component --}}
                @if($raffle->draw_at && $raffle->status->value === 'active')
                    <div class="bg-white/10 backdrop-blur-md rounded-xl p-4 border border-white/20"
                         x-data="countdown('{{ $raffle->draw_at->toISOString() }}')"
                         x-init="init()">
                        <p class="text-white text-xs font-bold uppercase mb-2 text-center tracking-widest">Termina en</p>
                        <div class="flex gap-2 lg:gap-3">
                            <div class="flex flex-col items-center min-w-[50px] lg:min-w-[60px]">
                                <div class="bg-[#13ec13] flex h-10 lg:h-12 w-full items-center justify-center rounded-lg shadow-lg">
                                    <p class="text-black text-lg lg:text-xl font-bold" x-text="days">00</p>
                                </div>
                                <p class="text-white text-[10px] mt-1 font-bold uppercase">Días</p>
                            </div>
                            <div class="flex flex-col items-center min-w-[50px] lg:min-w-[60px]">
                                <div class="bg-[#13ec13] flex h-10 lg:h-12 w-full items-center justify-center rounded-lg shadow-lg">
                                    <p class="text-black text-lg lg:text-xl font-bold" x-text="hours">00</p>
                                </div>
                                <p class="text-white text-[10px] mt-1 font-bold uppercase">Hrs</p>
                            </div>
                            <div class="flex flex-col items-center min-w-[50px] lg:min-w-[60px]">
                                <div class="bg-[#13ec13] flex h-10 lg:h-12 w-full items-center justify-center rounded-lg shadow-lg">
                                    <p class="text-black text-lg lg:text-xl font-bold" x-text="minutes">00</p>
                                </div>
                                <p class="text-white text-[10px] mt-1 font-bold uppercase">Min</p>
                            </div>
                            <div class="flex flex-col items-center min-w-[50px] lg:min-w-[60px]">
                                <div class="bg-[#13ec13] flex h-10 lg:h-12 w-full items-center justify-center rounded-lg shadow-lg">
                                    <p class="text-black text-lg lg:text-xl font-bold" x-text="seconds">00</p>
                                </div>
                                <p class="text-white text-[10px] mt-1 font-bold uppercase">Seg</p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="flex flex-col lg:flex-row gap-8 pt-10">
        {{-- Left Side: Selection & Info --}}
        <div class="flex-1 space-y-8">
            {{-- Progress Bar Component --}}
            <div class="bg-white dark:bg-[#1a2e1a] rounded-xl p-6 shadow-sm border border-[#dbe6db] dark:border-[#2a442a]">
                <div class="flex gap-4 justify-between mb-2">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#13ec13]">local_activity</span>
                        <p class="text-[#111811] dark:text-white text-lg font-bold">Boletos Vendidos</p>
                    </div>
                    <p class="text-[#111811] dark:text-[#13ec13] text-lg font-bold">{{ $raffle->sold_percentage }}%</p>
                </div>
                <div class="h-3 rounded-full bg-[#dbe6db] dark:bg-[#102210] overflow-hidden">
                    <div class="h-full bg-[#13ec13] transition-all duration-500" style="width: {{ min($raffle->sold_percentage, 100) }}%;"></div>
                </div>
                @if($raffle->available_tickets < 100 && $raffle->status->value === 'active')
                    <p class="text-[#618961] dark:text-[#13ec13]/70 text-sm mt-3 font-medium flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm">bolt</span>
                        ¡Apúrate! Solo quedan {{ number_format($raffle->available_tickets) }} boletos.
                    </p>
                @else
                    <p class="text-[#618961] dark:text-white/60 text-sm mt-3 font-medium">
                        {{ number_format($raffle->available_tickets) }} boletos disponibles de {{ number_format($raffle->total_tickets) }}
                    </p>
                @endif
            </div>

            {{-- Section Header --}}
            @if($raffle->status->canPurchase())
                <div class="border-l-4 border-[#13ec13] pl-4">
                    <h2 class="text-[#111811] dark:text-white text-2xl font-extrabold tracking-tight">Selecciona tu Paquete</h2>
                    <p class="text-[#618961] dark:text-white/60">Elige el mejor paquete y aumenta tus oportunidades de ganar.</p>
                </div>

                {{-- Ticket Package Grid --}}
                @if($raffle->packages->count() > 0)
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($raffle->packages as $package)
                            <div
                                wire:click="selectPackage({{ $package->id }})"
                                class="group relative cursor-pointer border-2 p-6 rounded-xl transition-all
                                    {{ $selectedPackageId === $package->id
                                        ? 'border-[#13ec13] bg-[#13ec13]/5 dark:bg-[#13ec13]/10 ring-1 ring-[#13ec13] ring-opacity-30'
                                        : 'border-[#dbe6db] dark:border-[#2a442a] hover:border-[#13ec13] bg-white dark:bg-[#1a2e1a]' }}"
                            >
                                @if($package->is_recommended)
                                    <div class="absolute -top-3 left-1/2 -translate-x-1/2 bg-[#13ec13] text-black text-[10px] font-black uppercase px-3 py-1 rounded-full shadow-lg">
                                        Mejor Valor
                                    </div>
                                @endif
                                <div class="flex justify-between items-start mb-4 {{ $package->is_recommended ? 'pt-2' : '' }}">
                                    <h3 class="text-xl font-bold text-[#111811] dark:text-white">{{ $package->name }}</h3>
                                    <span class="text-2xl font-extrabold text-[#13ec13]">{{ $package->formatted_price }}</span>
                                </div>
                                <p class="text-sm text-[#618961] dark:text-white/60 mb-6">
                                    {{ $package->quantity }} {{ $package->quantity === 1 ? 'boleto' : 'boletos' }}.
                                    @if($package->discount_percentage)
                                        <span class="text-[#13ec13] font-medium">({{ $package->discount_percentage }}% descuento)</span>
                                    @endif
                                </p>
                                <button
                                    type="button"
                                    class="w-full py-2.5 font-bold rounded-lg transition-colors
                                        {{ $selectedPackageId === $package->id
                                            ? 'bg-[#13ec13] text-black'
                                            : 'border border-[#13ec13] text-[#13ec13] group-hover:bg-[#13ec13] group-hover:text-black' }}"
                                >
                                    {{ $selectedPackageId === $package->id ? 'Seleccionado' : 'Seleccionar' }}
                                </button>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Custom Quantity --}}
                @if($raffle->allow_custom_quantity)
                    <div class="bg-white dark:bg-[#1a2e1a] rounded-xl p-6 border border-[#dbe6db] dark:border-[#2a442a]">
                        <h3 class="text-lg font-bold text-[#111811] dark:text-white mb-4">O elige la cantidad</h3>
                        <div class="flex items-center justify-center gap-6">
                            <button
                                wire:click="decrementQuantity"
                                class="size-12 rounded-full border-2 border-[#dbe6db] dark:border-[#2a442a] hover:border-[#13ec13] flex items-center justify-center transition-colors"
                            >
                                <span class="material-symbols-outlined">remove</span>
                            </button>
                            <span class="min-w-[80px] text-center text-3xl font-bold text-[#111811] dark:text-white">{{ $quantity }}</span>
                            <button
                                wire:click="incrementQuantity"
                                class="size-12 rounded-full border-2 border-[#dbe6db] dark:border-[#2a442a] hover:border-[#13ec13] flex items-center justify-center transition-colors"
                            >
                                <span class="material-symbols-outlined">add</span>
                            </button>
                        </div>
                        <p class="text-center text-[#618961] dark:text-white/60 mt-3 text-sm">
                            Precio por boleto: {{ $raffle->formatted_price }}
                        </p>
                    </div>
                @endif
            @endif

            {{-- Description Section --}}
            @if($raffle->description)
                <div class="pt-6">
                    <div class="border-l-4 border-[#13ec13] pl-4 mb-6">
                        <h2 class="text-[#111811] dark:text-white text-2xl font-extrabold tracking-tight">Descripción del Premio</h2>
                    </div>
                    <div class="prose dark:prose-invert max-w-none bg-white dark:bg-[#1a2e1a] rounded-xl p-6 border border-[#dbe6db] dark:border-[#2a442a]">
                        {!! $raffle->description !!}
                    </div>
                </div>
            @endif

            {{-- Prizes Section --}}
            @if($raffle->activePrizes->count() > 0)
                <div class="pt-6">
                    <div class="border-l-4 border-[#13ec13] pl-4 mb-6">
                        <h2 class="text-[#111811] dark:text-white text-2xl font-extrabold tracking-tight">Premios del Sorteo</h2>
                        <p class="text-[#618961] dark:text-white/60">Múltiples oportunidades de ganar en este sorteo.</p>
                    </div>
                    <div class="space-y-4">
                        @foreach($raffle->activePrizes as $prize)
                            @php
                                $prizeColors = [
                                    1 => ['bg' => 'bg-gradient-to-r from-yellow-500 to-amber-600', 'icon' => 'emoji_events', 'border' => 'border-yellow-500/30'],
                                    2 => ['bg' => 'bg-gradient-to-r from-gray-400 to-gray-500', 'icon' => 'military_tech', 'border' => 'border-gray-400/30'],
                                    3 => ['bg' => 'bg-gradient-to-r from-orange-600 to-orange-700', 'icon' => 'workspace_premium', 'border' => 'border-orange-600/30'],
                                ];
                                $colors = $prizeColors[$prize->prize_position] ?? ['bg' => 'bg-gradient-to-r from-[#13ec13] to-green-600', 'icon' => 'star', 'border' => 'border-[#13ec13]/30'];

                                $conditionLabels = [
                                    'exact_match' => 'Número exacto',
                                    'reverse' => 'Número al revés',
                                    'permutation' => 'Cualquier permutación',
                                    'last_digits' => 'Últimos ' . ($prize->winning_conditions['digit_count'] ?? 2) . ' dígitos',
                                    'first_digits' => 'Primeros ' . ($prize->winning_conditions['digit_count'] ?? 2) . ' dígitos',
                                    'combination' => 'Combinación especial',
                                ];
                                $conditionLabel = $conditionLabels[$prize->winning_conditions['type'] ?? ''] ?? 'Condición especial';
                            @endphp
                            <div class="bg-white dark:bg-[#1a2e1a] rounded-xl p-5 border {{ $colors['border'] }} flex items-center gap-4 hover:shadow-lg transition-shadow">
                                <div class="{{ $colors['bg'] }} text-white size-14 rounded-xl flex items-center justify-center shadow-lg flex-shrink-0">
                                    <span class="material-symbols-outlined text-2xl">{{ $colors['icon'] }}</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-1">
                                        <h3 class="text-lg font-bold text-[#111811] dark:text-white truncate">{{ $prize->name }}</h3>
                                        @if($prize->prize_position <= 3)
                                            <span class="text-xs font-bold uppercase px-2 py-0.5 rounded-full {{ $prize->prize_position === 1 ? 'bg-yellow-500/20 text-yellow-600 dark:text-yellow-400' : ($prize->prize_position === 2 ? 'bg-gray-400/20 text-gray-600 dark:text-gray-300' : 'bg-orange-500/20 text-orange-600 dark:text-orange-400') }}">
                                                {{ $prize->prize_position }}° Lugar
                                            </span>
                                        @endif
                                    </div>
                                    @if($prize->description)
                                        <p class="text-sm text-[#618961] dark:text-white/60 mb-2 line-clamp-2">{{ $prize->description }}</p>
                                    @endif
                                    <div class="flex items-center gap-2 text-xs text-[#618961] dark:text-white/50">
                                        <span class="material-symbols-outlined text-sm">casino</span>
                                        <span>{{ $conditionLabel }}</span>
                                    </div>
                                </div>
                                <div class="text-right flex-shrink-0">
                                    <p class="text-2xl font-black text-[#13ec13]">{{ $prize->formatted_value }}</p>
                                    <p class="text-xs text-[#618961] dark:text-white/50">Valor del premio</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-4 p-4 bg-[#f0f4f0] dark:bg-[#102210] rounded-lg">
                        <p class="text-sm text-[#618961] dark:text-white/60 flex items-start gap-2">
                            <span class="material-symbols-outlined text-[#13ec13] flex-shrink-0">info</span>
                            <span>Los premios se determinan según el número ganador de la lotería oficial. Un boleto puede ganar múltiples premios si cumple con varias condiciones.</span>
                        </p>
                    </div>
                </div>
            @endif

            {{-- FAQ Section --}}
            <div class="pt-6 space-y-6">
                <h3 class="text-xl font-bold text-[#111811] dark:text-white border-b border-[#dbe6db] dark:border-[#2a442a] pb-4">Preguntas Frecuentes</h3>
                <div class="space-y-4">
                    <details class="group bg-white dark:bg-[#1a2e1a] rounded-lg p-4 border border-[#dbe6db] dark:border-[#2a442a] open:ring-1 open:ring-[#13ec13]/30" open>
                        <summary class="flex items-center justify-between font-bold cursor-pointer list-none text-[#111811] dark:text-white">
                            ¿Cómo se elige al ganador?
                            <span class="material-symbols-outlined transition group-open:rotate-180">expand_more</span>
                        </summary>
                        <p class="text-sm text-[#618961] dark:text-white/60 mt-3 leading-relaxed">
                            Nuestros ganadores se seleccionan a través de un proceso de sorteo aleatorio totalmente transparente y certificado. Utilizamos un Generador de Números Aleatorios (RNG) verificado y transmitimos el proceso de selección en vivo en nuestras redes sociales.
                        </p>
                    </details>
                    <details class="group bg-white dark:bg-[#1a2e1a] rounded-lg p-4 border border-[#dbe6db] dark:border-[#2a442a]">
                        <summary class="flex items-center justify-between font-bold cursor-pointer list-none text-[#111811] dark:text-white">
                            ¿Cuándo se realiza el sorteo?
                            <span class="material-symbols-outlined transition group-open:rotate-180">expand_more</span>
                        </summary>
                        <p class="text-sm text-[#618961] dark:text-white/60 mt-3 leading-relaxed">
                            @if($raffle->draw_at)
                                El sorteo se realizará el {{ $raffle->draw_at->format('d/m/Y') }} a las {{ $raffle->draw_at->format('H:i') }} horas, o cuando se agoten todos los boletos, lo que ocurra primero.
                            @else
                                El sorteo se realizará cuando se agoten todos los boletos disponibles.
                            @endif
                        </p>
                    </details>
                    <details class="group bg-white dark:bg-[#1a2e1a] rounded-lg p-4 border border-[#dbe6db] dark:border-[#2a442a]">
                        <summary class="flex items-center justify-between font-bold cursor-pointer list-none text-[#111811] dark:text-white">
                            ¿Cómo me notifican si gano?
                            <span class="material-symbols-outlined transition group-open:rotate-180">expand_more</span>
                        </summary>
                        <p class="text-sm text-[#618961] dark:text-white/60 mt-3 leading-relaxed">
                            Si resultas ganador, te contactaremos inmediatamente por correo electrónico y WhatsApp al número que proporcionaste durante la compra. También anunciaremos al ganador en nuestras redes sociales.
                        </p>
                    </details>
                </div>
            </div>
        </div>

        {{-- Sticky Sidebar --}}
        <aside class="w-full lg:w-[350px]">
            <div class="sticky top-24 space-y-4">
                @if($raffle->status->canPurchase())
                    <div class="bg-white dark:bg-[#1a2e1a] rounded-xl shadow-xl border border-[#dbe6db] dark:border-[#2a442a] overflow-hidden">
                        <div class="bg-[#111811] p-4 text-white">
                            <h4 class="font-bold flex items-center gap-2">
                                <span class="material-symbols-outlined text-[#13ec13]">shopping_cart</span>
                                Tu Selección
                            </h4>
                        </div>
                        <div class="p-6 space-y-6">
                            <div class="flex justify-between items-center border-b border-[#dbe6db] dark:border-[#2a442a] pb-4">
                                <div>
                                    <p class="font-bold text-[#111811] dark:text-white">
                                        @if($this->selectedPackage)
                                            {{ $this->selectedPackage->name }}
                                        @else
                                            Boletos individuales
                                        @endif
                                    </p>
                                    <p class="text-xs text-[#618961] dark:text-white/60">{{ $quantity }} {{ $quantity === 1 ? 'Boleto' : 'Boletos' }}</p>
                                </div>
                                <p class="font-bold text-[#13ec13]">${{ number_format($this->subtotal / 100, 0, ',', '.') }}</p>
                            </div>
                            <div class="space-y-2">
                                <div class="flex justify-between text-sm">
                                    <span class="text-[#618961] dark:text-white/60">Subtotal</span>
                                    <span class="text-[#111811] dark:text-white">${{ number_format($this->subtotal / 100, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-[#618961] dark:text-white/60">Comisión</span>
                                    <span class="text-[#13ec13] font-medium">$0</span>
                                </div>
                                <div class="flex justify-between items-center pt-4 border-t border-[#dbe6db] dark:border-[#2a442a]">
                                    <span class="text-lg font-extrabold text-[#111811] dark:text-white">Total</span>
                                    <span class="text-2xl font-black text-[#13ec13]">${{ number_format($this->subtotal / 100, 0, ',', '.') }}</span>
                                </div>
                            </div>
                            <button
                                wire:click="addToCart"
                                class="w-full bg-[#13ec13] hover:brightness-110 text-black py-4 rounded-xl font-extrabold text-lg shadow-lg flex items-center justify-center gap-2 group transition-all"
                            >
                                <span>AGREGAR AL CARRITO</span>
                                <span class="material-symbols-outlined group-hover:translate-x-1 transition-transform">arrow_forward</span>
                            </button>
                            <div class="flex flex-col gap-2 items-center">
                                <p class="text-[10px] text-[#618961] uppercase font-bold tracking-widest">Pago 100% Seguro</p>
                                <div class="flex gap-2 opacity-50">
                                    <span class="material-symbols-outlined text-sm text-[#111811] dark:text-white">credit_card</span>
                                    <span class="material-symbols-outlined text-sm text-[#111811] dark:text-white">lock</span>
                                    <span class="material-symbols-outlined text-sm text-[#111811] dark:text-white">verified_user</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    {{-- Status Message for Non-Purchasable --}}
                    <div class="bg-white dark:bg-[#1a2e1a] rounded-xl shadow-xl border border-[#dbe6db] dark:border-[#2a442a] overflow-hidden">
                        <div class="bg-gray-600 p-4 text-white">
                            <h4 class="font-bold flex items-center gap-2">
                                <span class="material-symbols-outlined">info</span>
                                Estado del Sorteo
                            </h4>
                        </div>
                        <div class="p-6 text-center">
                            @if($raffle->status->value === 'upcoming')
                                <span class="material-symbols-outlined text-5xl text-blue-500 mb-4">schedule</span>
                                <p class="text-lg font-bold text-[#111811] dark:text-white mb-2">Próximamente</p>
                                <p class="text-[#618961] dark:text-white/60 text-sm">Este sorteo aún no está disponible para compra.</p>
                            @elseif($raffle->status->value === 'completed')
                                <span class="material-symbols-outlined text-5xl text-gray-400 mb-4">emoji_events</span>
                                <p class="text-lg font-bold text-[#111811] dark:text-white mb-2">Sorteo Finalizado</p>
                                <p class="text-[#618961] dark:text-white/60 text-sm">Este sorteo ya ha concluido.</p>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- WhatsApp Support Widget --}}
                <a
                    href="https://wa.me/573001234567?text=Hola,%20tengo%20una%20consulta%20sobre%20el%20sorteo:%20{{ urlencode($raffle->title) }}"
                    target="_blank"
                    class="flex items-center gap-3 bg-[#25D366] text-white p-4 rounded-xl shadow-md hover:brightness-105 transition-all"
                >
                    <div class="bg-white/20 rounded-full p-2">
                        <svg class="size-6 fill-current" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    </div>
                    <div>
                        <p class="font-bold text-sm">¿Necesitas ayuda?</p>
                        <p class="text-xs opacity-90">Chatea con nosotros por WhatsApp</p>
                    </div>
                </a>

                {{-- Terms Notice --}}
                <div class="bg-[#f0f4f0] dark:bg-[#102210] rounded-lg p-4 text-center">
                    <p class="text-xs text-[#618961] dark:text-white/60">
                        Al participar aceptas los
                        <a href="{{ route('page.show', 'terminos-y-condiciones') }}" class="text-[#13ec13] hover:underline" wire:navigate>
                            términos y condiciones
                        </a>
                    </p>
                </div>
            </div>
        </aside>
    </div>

    {{-- Image Gallery (if multiple images) --}}
    @if($raffle->images->count() > 1)
        <div class="mt-12">
            <div class="border-l-4 border-[#13ec13] pl-4 mb-6">
                <h2 class="text-[#111811] dark:text-white text-2xl font-extrabold tracking-tight">Galería de Imágenes</h2>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach($raffle->images as $image)
                    <div class="aspect-video overflow-hidden rounded-lg bg-[#f0f4f0] dark:bg-[#1a2e1a]">
                        <img
                            src="{{ $image->url }}"
                            alt="{{ $image->alt_text ?? $raffle->title }}"
                            class="h-full w-full object-cover hover:scale-105 transition-transform duration-300"
                        >
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

@script
<script>
    Alpine.data('countdown', (targetDate) => ({
        days: '00',
        hours: '00',
        minutes: '00',
        seconds: '00',
        interval: null,

        init() {
            this.updateCountdown();
            this.interval = setInterval(() => this.updateCountdown(), 1000);
        },

        updateCountdown() {
            const target = new Date(targetDate).getTime();
            const now = new Date().getTime();
            const diff = target - now;

            if (diff <= 0) {
                this.days = '00';
                this.hours = '00';
                this.minutes = '00';
                this.seconds = '00';
                clearInterval(this.interval);
                return;
            }

            this.days = String(Math.floor(diff / (1000 * 60 * 60 * 24))).padStart(2, '0');
            this.hours = String(Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60))).padStart(2, '0');
            this.minutes = String(Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60))).padStart(2, '0');
            this.seconds = String(Math.floor((diff % (1000 * 60)) / 1000)).padStart(2, '0');
        },

        destroy() {
            if (this.interval) clearInterval(this.interval);
        }
    }));
</script>
@endscript
