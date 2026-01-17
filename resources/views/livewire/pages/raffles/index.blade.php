<div>
    {{-- Headline --}}
    <div class="mb-8">
        <h1 class="text-[#111811] dark:text-white tracking-tight text-[32px] font-bold leading-tight">Explorar Sorteos</h1>
        <p class="text-[#618961] dark:text-white/60 text-base">Encuentra y participa en los mejores sorteos activos del momento.</p>
    </div>

    {{-- Filters Section --}}
    <div class="flex flex-col gap-6 mb-8">
        {{-- Tabs for Status --}}
        <div class="border-b border-[#dbe6db] dark:border-white/10">
            <div class="flex gap-8 overflow-x-auto no-scrollbar">
                <button
                    wire:click="$set('status', '')"
                    class="flex flex-col items-center justify-center border-b-[3px] pb-3 pt-2 transition-colors {{ $status === '' ? 'border-[#13ec13] text-[#13ec13]' : 'border-transparent text-[#618961] hover:text-[#111811] dark:hover:text-white' }}"
                >
                    <p class="text-sm font-bold tracking-wide">Todos</p>
                </button>
                <button
                    wire:click="$set('status', 'active')"
                    class="flex flex-col items-center justify-center border-b-[3px] pb-3 pt-2 transition-colors {{ $status === 'active' ? 'border-[#13ec13] text-[#13ec13]' : 'border-transparent text-[#618961] hover:text-[#111811] dark:hover:text-white' }}"
                >
                    <p class="text-sm font-bold tracking-wide">Activo</p>
                </button>
                <button
                    wire:click="$set('status', 'upcoming')"
                    class="flex flex-col items-center justify-center border-b-[3px] pb-3 pt-2 transition-colors {{ $status === 'upcoming' ? 'border-[#13ec13] text-[#13ec13]' : 'border-transparent text-[#618961] hover:text-[#111811] dark:hover:text-white' }}"
                >
                    <p class="text-sm font-bold tracking-wide">Próximo</p>
                </button>
                <button
                    wire:click="$set('status', 'completed')"
                    class="flex flex-col items-center justify-center border-b-[3px] pb-3 pt-2 transition-colors {{ $status === 'completed' ? 'border-[#13ec13] text-[#13ec13]' : 'border-transparent text-[#618961] hover:text-[#111811] dark:hover:text-white' }}"
                >
                    <p class="text-sm font-bold tracking-wide">Finalizado</p>
                </button>
            </div>
        </div>

        {{-- Results Count --}}
        <div class="flex items-center justify-end">
            <div class="text-[#618961] text-sm font-medium">
                Mostrando <span class="text-[#111811] dark:text-white">{{ $raffles->count() }}</span> sorteos
            </div>
        </div>
    </div>

    {{-- Raffle Grid --}}
    @if($raffles->count() > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-12">
            @foreach($raffles as $raffle)
                @php
                    $statusColor = match($raffle->status->value) {
                        'active' => 'bg-[#13ec13]',
                        'upcoming' => 'bg-blue-500',
                        'completed' => 'bg-gray-600',
                        default => 'bg-[#13ec13]'
                    };
                    $progressColor = match($raffle->status->value) {
                        'active' => $raffle->sold_percentage > 90 ? 'bg-orange-500' : 'bg-[#13ec13]',
                        'upcoming' => 'bg-blue-500',
                        'completed' => 'bg-gray-400',
                        default => 'bg-[#13ec13]'
                    };
                    $isFinished = $raffle->status->value === 'completed';
                @endphp
                <div class="group bg-white dark:bg-white/5 rounded-xl border border-[#dbe6db] dark:border-white/10 overflow-hidden hover:shadow-xl hover:shadow-[#13ec13]/5 transition-all duration-300 flex flex-col {{ $isFinished ? 'grayscale opacity-75' : '' }}">
                    <div class="relative w-full aspect-[16/10] overflow-hidden">
                        <div class="w-full h-full bg-center bg-cover transition-transform duration-500 group-hover:scale-105" style="background-image: url('{{ $raffle->primaryImage?->url ?? 'https://placehold.co/400x250/1a2e1a/13ec13?text=' . urlencode($raffle->title) }}');"></div>
                        <div class="absolute top-3 left-3 px-3 py-1 {{ $statusColor }} text-white text-[10px] font-bold uppercase tracking-wider rounded-full flex items-center gap-1 shadow-lg">
                            @if($raffle->sold_percentage > 90 && $raffle->status->value === 'active')
                                <span class="material-symbols-outlined text-[14px]">bolt</span> Últimos cupos
                            @elseif($raffle->status->value === 'upcoming')
                                <span class="material-symbols-outlined text-[14px]">schedule</span> Próximamente
                            @elseif($raffle->status->value === 'completed')
                                <span class="material-symbols-outlined text-[14px]">check_circle</span> Finalizado
                            @else
                                {{ $raffle->status->getLabel() }}
                            @endif
                        </div>
                    </div>
                    <div class="p-5 flex flex-col grow">
                        <div class="mb-2">
                            <h3 class="text-[#111811] dark:text-white text-lg font-bold leading-snug line-clamp-2">{{ $raffle->title }}</h3>
                            <p class="{{ $isFinished ? 'text-gray-500' : 'text-[#13ec13]' }} font-bold text-xl mt-1">{{ $raffle->formatted_price }}</p>
                        </div>
                        <div class="mt-auto pt-4">
                            <div class="flex items-center justify-between text-xs font-semibold mb-1.5">
                                <span class="text-[#618961] dark:text-white/60">
                                    @if($raffle->status->value === 'upcoming')
                                        Inicia pronto
                                    @else
                                        Progreso
                                    @endif
                                </span>
                                <span class="{{ $raffle->sold_percentage > 90 && $raffle->status->value === 'active' ? 'text-orange-500' : ($isFinished ? 'text-gray-500' : 'text-[#13ec13]') }}">
                                    @if($isFinished)
                                        Agotado
                                    @else
                                        {{ $raffle->sold_percentage }}% vendido
                                    @endif
                                </span>
                            </div>
                            <div class="w-full bg-[#f0f4f0] dark:bg-white/10 h-2 rounded-full overflow-hidden">
                                <div class="{{ $progressColor }} h-full rounded-full" style="width: {{ min($raffle->sold_percentage, 100) }}%;"></div>
                            </div>
                            @if($raffle->status->value === 'active')
                                <a href="{{ route('raffles.show', $raffle) }}" class="w-full mt-5 py-3 bg-[#13ec13] hover:bg-[#13ec13]/90 text-white font-bold rounded-lg transition-colors flex items-center justify-center gap-2" wire:navigate>
                                    <span class="material-symbols-outlined text-[18px]">shopping_cart</span> Comprar Boleto
                                </a>
                            @elseif($raffle->status->value === 'upcoming')
                                <a href="{{ route('raffles.show', $raffle) }}" class="w-full mt-5 py-3 bg-[#f0f4f0] dark:bg-white/10 text-[#618961] dark:text-white/60 font-bold rounded-lg transition-colors flex items-center justify-center gap-2" wire:navigate>
                                    <span class="material-symbols-outlined text-[18px]">visibility</span> Ver Detalles
                                </a>
                            @else
                                <a href="{{ route('raffles.show', $raffle) }}" class="w-full mt-5 py-3 border-2 border-[#dbe6db] dark:border-white/10 text-[#111811] dark:text-white font-bold rounded-lg hover:bg-[#f0f4f0] dark:hover:bg-white/5 transition-colors flex items-center justify-center gap-2" wire:navigate>
                                    <span class="material-symbols-outlined text-[18px]">emoji_events</span> Ver Resultados
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="flex items-center justify-center gap-2 py-8 border-t border-[#dbe6db] dark:border-white/10">
            {{ $raffles->links() }}
        </div>
    @else
        <div class="text-center py-16">
            <span class="material-symbols-outlined text-6xl text-gray-300 mb-4">confirmation_number</span>
            <h3 class="text-xl font-bold mb-2">No hay sorteos disponibles</h3>
            <p class="text-[#618961]">No se encontraron sorteos con los filtros seleccionados.</p>
        </div>
    @endif
</div>
