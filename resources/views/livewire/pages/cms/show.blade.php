<div>
    {{-- Header --}}
    <div class="mb-8">
        <h1 class="text-[#111811] dark:text-white text-3xl md:text-4xl font-extrabold tracking-tight">{{ $page->title }}</h1>
    </div>

    {{-- Special layout for "Cómo Funciona" page --}}
    @if($page->slug === 'como-funciona')
        {{-- Hero Section --}}
        <div class="bg-[#13ec13]/5 dark:bg-[#13ec13]/5 rounded-3xl p-8 md:p-12 mb-8">
            <div class="text-center mb-12">
                <h2 class="text-2xl md:text-3xl font-extrabold text-[#111811] dark:text-white mb-2">Participa en 3 simples pasos</h2>
                <p class="text-[#618961] dark:text-white/60">Ganar nunca fue tan fácil</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 md:gap-12">
                <div class="flex flex-col items-center text-center">
                    <div class="size-16 rounded-full bg-[#13ec13] flex items-center justify-center mb-6 shadow-xl shadow-[#13ec13]/20 text-black">
                        <span class="material-symbols-outlined text-3xl">touch_app</span>
                    </div>
                    <h3 class="text-lg font-bold text-[#111811] dark:text-white mb-2">1. Elige tu sorteo</h3>
                    <p class="text-sm text-[#618961] dark:text-white/60">Explora nuestra selección de sorteos activos y elige el premio que más te guste.</p>
                </div>
                <div class="flex flex-col items-center text-center">
                    <div class="size-16 rounded-full bg-[#13ec13] flex items-center justify-center mb-6 shadow-xl shadow-[#13ec13]/20 text-black">
                        <span class="material-symbols-outlined text-3xl">payments</span>
                    </div>
                    <h3 class="text-lg font-bold text-[#111811] dark:text-white mb-2">2. Compra tus boletos</h3>
                    <p class="text-sm text-[#618961] dark:text-white/60">Selecciona un paquete o elige la cantidad exacta. El pago es 100% seguro.</p>
                </div>
                <div class="flex flex-col items-center text-center">
                    <div class="size-16 rounded-full bg-[#13ec13] flex items-center justify-center mb-6 shadow-xl shadow-[#13ec13]/20 text-black">
                        <span class="material-symbols-outlined text-3xl">emoji_events</span>
                    </div>
                    <h3 class="text-lg font-bold text-[#111811] dark:text-white mb-2">3. Recibe tus números</h3>
                    <p class="text-sm text-[#618961] dark:text-white/60">Tus boletos se asignan automáticamente. ¡Espera el sorteo y gana!</p>
                </div>
            </div>
        </div>

        {{-- Winner Selection Section --}}
        <div class="bg-white dark:bg-[#1a2e1a] rounded-xl p-6 md:p-8 border border-[#dbe6db] dark:border-[#2a442a] mb-8">
            <div class="flex items-start gap-4">
                <div class="size-12 rounded-full bg-[#13ec13]/10 flex items-center justify-center flex-shrink-0">
                    <span class="material-symbols-outlined text-2xl text-[#13ec13]">verified</span>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-[#111811] dark:text-white mb-2">¿Cómo se determina el ganador?</h3>
                    <p class="text-[#618961] dark:text-white/60 leading-relaxed">
                        Utilizamos los resultados oficiales de las loterías nacionales para determinar el número ganador de manera
                        <strong class="text-[#111811] dark:text-white">100% transparente y verificable</strong>.
                        La fórmula de cálculo está publicada en cada sorteo, garantizando total imparcialidad.
                    </p>
                </div>
            </div>
        </div>

        {{-- Security Features --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            <div class="bg-white dark:bg-[#1a2e1a] rounded-xl p-6 border border-[#dbe6db] dark:border-[#2a442a] text-center">
                <span class="material-symbols-outlined text-3xl text-[#13ec13] mb-3">lock</span>
                <h4 class="font-bold text-[#111811] dark:text-white mb-1">Pagos Seguros</h4>
                <p class="text-sm text-[#618961] dark:text-white/60">Pasarelas certificadas y encriptación SSL</p>
            </div>
            <div class="bg-white dark:bg-[#1a2e1a] rounded-xl p-6 border border-[#dbe6db] dark:border-[#2a442a] text-center">
                <span class="material-symbols-outlined text-3xl text-[#13ec13] mb-3">shield</span>
                <h4 class="font-bold text-[#111811] dark:text-white mb-1">Sorteos Transparentes</h4>
                <p class="text-sm text-[#618961] dark:text-white/60">Resultados basados en loterías oficiales</p>
            </div>
            <div class="bg-white dark:bg-[#1a2e1a] rounded-xl p-6 border border-[#dbe6db] dark:border-[#2a442a] text-center">
                <span class="material-symbols-outlined text-3xl text-[#13ec13] mb-3">support_agent</span>
                <h4 class="font-bold text-[#111811] dark:text-white mb-1">Soporte 24/7</h4>
                <p class="text-sm text-[#618961] dark:text-white/60">Atención por WhatsApp y correo</p>
            </div>
        </div>

        {{-- CTA Section --}}
        <div class="bg-[#111811] rounded-xl p-8 text-center">
            <h3 class="text-2xl font-bold text-white mb-2">¿Listo para participar?</h3>
            <p class="text-white/60 mb-6">Explora nuestros sorteos activos y encuentra tu premio</p>
            <a href="{{ route('raffles.index') }}" class="inline-flex items-center gap-2 bg-[#13ec13] text-black px-8 py-3 rounded-lg font-bold hover:brightness-110 transition-all" wire:navigate>
                <span class="material-symbols-outlined">confirmation_number</span>
                Ver Sorteos
            </a>
        </div>

    {{-- Regular content for other pages --}}
    @elseif($page->content)
        <div class="bg-white dark:bg-[#1a2e1a] rounded-xl p-6 md:p-8 border border-[#dbe6db] dark:border-[#2a442a]">
            <div class="prose prose-lg dark:prose-invert prose-headings:text-[#111811] dark:prose-headings:text-white prose-a:text-[#13ec13] prose-strong:text-[#111811] dark:prose-strong:text-white prose-h2:text-2xl prose-h2:font-bold prose-h2:mt-8 prose-h2:mb-4 prose-h3:text-lg prose-h3:font-bold prose-h3:mt-6 prose-h3:mb-2 prose-p:text-[#618961] dark:prose-p:text-white/70 prose-p:leading-relaxed prose-li:text-[#618961] dark:prose-li:text-white/70 max-w-none">
                {!! $page->content !!}
            </div>
        </div>
    @endif

    {{-- FAQ Sections (for FAQ page) --}}
    @if($page->slug === 'preguntas-frecuentes' && $page->sections)
        <div class="mt-8 space-y-4">
            <h2 class="text-2xl font-bold text-[#111811] dark:text-white border-b border-[#dbe6db] dark:border-[#2a442a] pb-4">Preguntas Frecuentes</h2>
            @foreach($page->sections as $section)
                <details class="group bg-white dark:bg-[#1a2e1a] rounded-lg p-4 border border-[#dbe6db] dark:border-[#2a442a] open:ring-1 open:ring-[#13ec13]/30">
                    <summary class="flex items-center justify-between font-bold cursor-pointer list-none text-[#111811] dark:text-white">
                        {{ $section['question'] ?? '' }}
                        <span class="material-symbols-outlined transition group-open:rotate-180">expand_more</span>
                    </summary>
                    <div class="prose dark:prose-invert prose-a:text-[#13ec13] text-sm text-[#618961] dark:text-white/60 mt-3 leading-relaxed">
                        {!! $section['answer'] ?? '' !!}
                    </div>
                </details>
            @endforeach
        </div>
    @endif

    {{-- WhatsApp Support (except for como-funciona which has its own CTA) --}}
    @if($page->slug !== 'como-funciona')
        <div class="mt-12">
            <a
                href="https://wa.me/573001234567?text=Hola,%20tengo%20una%20consulta%20sobre:%20{{ urlencode($page->title) }}"
                target="_blank"
                class="inline-flex items-center gap-3 bg-[#25D366] text-white px-6 py-3 rounded-xl shadow-md hover:brightness-105 transition-all"
            >
                <svg class="size-6 fill-current" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                <span class="font-bold">¿Tienes más dudas? Escríbenos</span>
            </a>
        </div>
    @endif
</div>
