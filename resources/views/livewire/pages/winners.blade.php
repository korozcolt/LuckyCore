<div>
    {{-- Page Header --}}
    <section class="py-6 lg:py-10">
        <div class="flex flex-col items-center text-center gap-3">
            <h1 class="text-3xl lg:text-4xl font-black">Nuestros Ganadores</h1>
            <p class="text-gray-500 dark:text-white/60 text-sm sm:text-base max-w-2xl">
                Conoce a las personas que ya han ganado increíbles premios en nuestros sorteos
            </p>
        </div>
    </section>

    {{-- Featured Testimonials --}}
    @if($featuredTestimonials->isNotEmpty())
        <section class="py-8 border-b border-gray-100 dark:border-[#2a442a]">
            <h2 class="text-xl font-bold mb-6 flex items-center gap-2">
                <span class="material-symbols-outlined text-[#13ec13]">star</span>
                Testimonios Destacados
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($featuredTestimonials as $testimonial)
                    <div class="bg-white dark:bg-[#1a2e1a] border border-gray-100 dark:border-[#2a442a] rounded-xl p-6">
                        <div class="flex items-start gap-4 mb-4">
                            @if($testimonial->photo_url)
                                <img src="{{ $testimonial->photo_url }}" alt="{{ $testimonial->display_name }}" class="w-16 h-16 rounded-full object-cover">
                            @else
                                <div class="w-16 h-16 rounded-full bg-[#13ec13]/20 flex items-center justify-center">
                                    <span class="material-symbols-outlined text-[#13ec13] text-2xl">emoji_events</span>
                                </div>
                            @endif
                            <div>
                                <p class="font-bold">{{ $testimonial->display_name }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $testimonial->winner->prize_name }}</p>
                                @if($testimonial->rating)
                                    <div class="text-yellow-500 text-sm">
                                        @for($i = 0; $i < $testimonial->rating; $i++)
                                            ⭐
                                        @endfor
                                    </div>
                                @endif
                            </div>
                        </div>
                        @if($testimonial->comment)
                            <p class="text-gray-600 dark:text-gray-300 text-sm italic">"{{ $testimonial->comment }}"</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Raffle Filter --}}
    @if($completedRaffles->isNotEmpty())
        <section class="py-6">
            <div class="flex items-center gap-3 overflow-x-auto pb-2">
                <button
                    wire:click="selectRaffle(null)"
                    class="px-4 py-2 rounded-full text-sm font-bold whitespace-nowrap transition-all {{ !$selectedRaffleId ? 'bg-[#13ec13] text-black' : 'bg-gray-100 dark:bg-[#1a2e1a] text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-[#2a442a]' }}"
                >
                    Todos los sorteos
                </button>
                @foreach($completedRaffles as $raffle)
                    <button
                        wire:click="selectRaffle({{ $raffle->id }})"
                        class="px-4 py-2 rounded-full text-sm font-bold whitespace-nowrap transition-all {{ $selectedRaffleId === $raffle->id ? 'bg-[#13ec13] text-black' : 'bg-gray-100 dark:bg-[#1a2e1a] text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-[#2a442a]' }}"
                    >
                        {{ $raffle->title }}
                    </button>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Winners Grid --}}
    <section class="py-8">
        <h2 class="text-xl font-bold mb-6 flex items-center gap-2">
            <span class="material-symbols-outlined text-[#13ec13]">emoji_events</span>
            Ganadores
            @if($selectedRaffleId)
                <span class="text-sm font-normal text-gray-500">
                    - {{ $completedRaffles->firstWhere('id', $selectedRaffleId)?->title }}
                </span>
            @endif
        </h2>

        @if($winners->isEmpty())
            <div class="text-center py-16 bg-gray-50 dark:bg-[#1a2e1a] rounded-xl">
                <span class="material-symbols-outlined text-6xl text-gray-300 dark:text-gray-600 mb-4">emoji_events</span>
                <p class="text-gray-500 dark:text-gray-400">Aún no hay ganadores registrados</p>
                <a href="{{ route('raffles.index') }}" class="mt-4 inline-block text-[#13ec13] font-bold hover:underline" wire:navigate>
                    Ver sorteos activos
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($winners as $winner)
                    <div class="bg-white dark:bg-[#1a2e1a] border border-gray-100 dark:border-[#2a442a] rounded-xl overflow-hidden hover:shadow-lg transition-all">
                        {{-- Raffle Image --}}
                        <div class="h-32 bg-cover bg-center relative" style="background-image: url('{{ $winner->raffle->primaryImage?->url ?? 'https://placehold.co/400x200/1a2e1a/13ec13?text=Ganador' }}');">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/80 to-transparent"></div>
                            <div class="absolute bottom-3 left-3 right-3">
                                <p class="text-white font-bold text-sm">{{ $winner->raffle->title }}</p>
                            </div>
                            <div class="absolute top-3 right-3">
                                <span class="bg-[#13ec13] text-black text-xs font-bold px-2 py-1 rounded-full">
                                    #{{ $winner->prize_position }}
                                </span>
                            </div>
                        </div>

                        {{-- Winner Info --}}
                        <div class="p-5">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-12 h-12 rounded-full bg-[#13ec13]/20 flex items-center justify-center">
                                    <span class="material-symbols-outlined text-[#13ec13]">person</span>
                                </div>
                                <div>
                                    <p class="font-bold">{{ $winner->display_name }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $winner->created_at->format('d M, Y') }}</p>
                                </div>
                            </div>

                            <div class="bg-gray-50 dark:bg-[#102210] rounded-lg p-3 mb-3">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <p class="text-[10px] uppercase font-bold text-gray-400">Premio</p>
                                        <p class="font-bold text-sm">{{ $winner->prize_name }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-[10px] uppercase font-bold text-gray-400">Número</p>
                                        <p class="font-mono text-[#13ec13] font-bold">{{ $winner->ticket_number }}</p>
                                    </div>
                                </div>
                            </div>

                            @if($winner->testimonial && $winner->testimonial->is_approved)
                                <div class="border-t border-gray-100 dark:border-[#2a442a] pt-3 mt-3">
                                    @if($winner->testimonial->rating)
                                        <div class="text-yellow-500 text-xs mb-1">
                                            @for($i = 0; $i < $winner->testimonial->rating; $i++)
                                                ⭐
                                            @endfor
                                        </div>
                                    @endif
                                    @if($winner->testimonial->comment)
                                        <p class="text-sm text-gray-600 dark:text-gray-300 italic line-clamp-2">
                                            "{{ $winner->testimonial->comment }}"
                                        </p>
                                    @endif
                                </div>
                            @endif

                            @if($winner->is_delivered)
                                <div class="flex items-center gap-1 text-xs text-green-600 mt-3">
                                    <span class="material-symbols-outlined text-sm">check_circle</span>
                                    Premio entregado
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="mt-8">
                {{ $winners->links() }}
            </div>
        @endif
    </section>
</div>
