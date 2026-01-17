<div>
    {{-- Hero Section with Featured Raffle --}}
    @if($featuredRaffles->count() > 0)
        @php $featured = $featuredRaffles->first(); @endphp
        <section class="py-4 lg:py-8">
            <div class="bg-white dark:bg-[#1a2e1a] rounded-xl overflow-hidden shadow-2xl border border-gray-100 dark:border-[#2a442a] flex flex-col lg:flex-row">
                {{-- Image Side --}}
                <div class="lg:w-3/5 h-[300px] lg:h-auto bg-cover bg-center relative" style="background-image: url('{{ $featured->primaryImage?->url ?? 'https://placehold.co/800x600/1a2e1a/13ec13?text=' . urlencode($featured->title) }}');">
                    <div class="m-6">
                        <span class="bg-[#13ec13]/90 text-black px-4 py-1.5 rounded-full text-xs font-black uppercase tracking-widest shadow-lg">Destacado</span>
                    </div>
                </div>
                {{-- Content Side --}}
                <div class="lg:w-2/5 p-8 lg:p-12 flex flex-col justify-center gap-6">
                    <div>
                        <h2 class="text-3xl lg:text-4xl font-black leading-tight mb-4">{{ $featured->title }}</h2>
                        <p class="text-gray-600 dark:text-gray-300">{{ $featured->short_description }}</p>
                    </div>
                    <div class="space-y-4">
                        <div class="flex justify-between items-end">
                            <span class="text-sm font-bold text-gray-500 uppercase tracking-tighter">Progreso de venta</span>
                            <span class="text-2xl font-black text-[#13ec13]">{{ $featured->sold_percentage }}%</span>
                        </div>
                        <div class="h-3 w-full bg-gray-100 dark:bg-[#102210] rounded-full overflow-hidden">
                            <div class="h-full bg-[#13ec13]" style="width: {{ min($featured->sold_percentage, 100) }}%;"></div>
                        </div>
                        <div class="flex justify-between items-center bg-[#f6f8f6] dark:bg-[#102210] p-4 rounded-lg">
                            <div>
                                <p class="text-[10px] uppercase font-bold text-gray-400">Precio Boleto</p>
                                <p class="text-xl font-black">{{ $featured->formatted_price }}</p>
                            </div>
                            <a href="{{ route('raffles.show', $featured) }}" class="bg-[#13ec13] text-black px-8 py-3 rounded-lg font-bold text-sm hover:scale-105 transition-transform shadow-lg shadow-[#13ec13]/20" wire:navigate>
                                COMPRAR AHORA
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif

    {{-- Section Header --}}
    <div class="flex items-center justify-between mb-8 border-b border-gray-100 dark:border-[#2a442a] pb-4">
        <h3 class="text-2xl font-bold tracking-tight">Sorteos Activos</h3>
        <a class="text-[#13ec13] text-sm font-bold hover:underline flex items-center gap-1" href="{{ route('raffles.index') }}" wire:navigate>
            Ver todos <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
        </a>
    </div>

    {{-- Featured Raffles Grid --}}
    <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 pb-12">
        @forelse($activeRaffles as $raffle)
            <div class="group bg-white dark:bg-[#1a2e1a] border border-gray-100 dark:border-[#2a442a] rounded-xl overflow-hidden hover:shadow-xl transition-all">
                <a href="{{ route('raffles.show', $raffle) }}" wire:navigate>
                    <div class="h-48 bg-cover bg-center overflow-hidden relative" style="background-image: url('{{ $raffle->primaryImage?->url ?? 'https://placehold.co/400x300/1a2e1a/13ec13?text=' . urlencode($raffle->title) }}');">
                        <div class="p-3">
                            <span class="bg-black/50 backdrop-blur-sm text-white text-[10px] px-2 py-1 rounded font-bold uppercase">
                                {{ $raffle->status->getLabel() }}
                            </span>
                        </div>
                    </div>
                </a>
                <div class="p-6">
                    <a href="{{ route('raffles.show', $raffle) }}" wire:navigate>
                        <h4 class="text-lg font-bold mb-1 hover:text-[#13ec13] transition-colors">{{ $raffle->title }}</h4>
                    </a>
                    <p class="text-[#13ec13] font-black text-sm mb-4">{{ $raffle->formatted_price }} x boleto</p>
                    <div class="space-y-2 mb-6">
                        <div class="flex justify-between text-[11px] font-bold uppercase text-gray-400">
                            <span>Vendido</span>
                            <span>{{ $raffle->sold_percentage }}%</span>
                        </div>
                        <div class="h-1.5 w-full bg-gray-100 dark:bg-[#102210] rounded-full overflow-hidden">
                            <div class="h-full bg-[#13ec13]" style="width: {{ min($raffle->sold_percentage, 100) }}%;"></div>
                        </div>
                    </div>
                    <a href="{{ route('raffles.show', $raffle) }}" class="block w-full bg-[#f0f4f0] dark:bg-[#203a20] text-black dark:text-white py-3 rounded-lg font-bold text-sm group-hover:bg-[#13ec13] group-hover:text-black transition-colors text-center" wire:navigate>
                        Ver detalles
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12">
                <span class="material-symbols-outlined text-6xl text-gray-300 mb-4">confirmation_number</span>
                <p class="text-gray-500">No hay sorteos activos en este momento.</p>
            </div>
        @endforelse
    </section>

    {{-- How it Works Section --}}
    <section class="bg-[#13ec13]/5 dark:bg-[#13ec13]/5 rounded-3xl p-12 mb-8">
        <div class="text-center mb-12">
            <h3 class="text-3xl font-black mb-2">¿Cómo Funciona?</h3>
            <p class="text-gray-500">Participar es muy sencillo y seguro</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
            <div class="flex flex-col items-center text-center">
                <div class="size-16 rounded-full bg-[#13ec13] flex items-center justify-center mb-6 shadow-xl shadow-[#13ec13]/20 text-black">
                    <span class="material-symbols-outlined text-3xl">touch_app</span>
                </div>
                <h5 class="text-lg font-bold mb-2">1. Elige tu premio</h5>
                <p class="text-sm text-gray-500">Explora nuestros sorteos activos y selecciona el que más te guste.</p>
            </div>
            <div class="flex flex-col items-center text-center">
                <div class="size-16 rounded-full bg-[#13ec13] flex items-center justify-center mb-6 shadow-xl shadow-[#13ec13]/20 text-black">
                    <span class="material-symbols-outlined text-3xl">payments</span>
                </div>
                <h5 class="text-lg font-bold mb-2">2. Compra tus boletos</h5>
                <p class="text-sm text-gray-500">Selecciona cuántos boletos quieres y paga de forma segura.</p>
            </div>
            <div class="flex flex-col items-center text-center">
                <div class="size-16 rounded-full bg-[#13ec13] flex items-center justify-center mb-6 shadow-xl shadow-[#13ec13]/20 text-black">
                    <span class="material-symbols-outlined text-3xl">emoji_events</span>
                </div>
                <h5 class="text-lg font-bold mb-2">3. ¡Gana!</h5>
                <p class="text-sm text-gray-500">Espera a que se complete la venta y consulta el ganador en vivo.</p>
            </div>
        </div>
    </section>
</div>
