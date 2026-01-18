<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light">
<head>
    @include('partials.head')
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
    </style>
</head>
<body class="min-h-screen bg-[#f6f8f6] dark:bg-[#102210] text-[#111811] dark:text-white transition-colors duration-200">
    {{-- Navigation Header --}}
    <header class="sticky top-0 z-50 bg-white dark:bg-[#102210] border-b border-[#dbe6db] dark:border-white/10 px-4 md:px-10 py-3">
        <div class="max-w-[1280px] mx-auto flex items-center justify-between gap-4">
            <div class="flex items-center gap-8">
                {{-- Logo --}}
                <a href="{{ route('home') }}" class="flex items-center gap-2 text-[#111811] dark:text-[#13ec13]" wire:navigate>
                    <img
                        src="{{ asset('images/logo.webp') }}"
                        alt="{{ config('app.name') }}"
                        class="h-10 md:h-11 w-auto"
                        loading="eager"
                    />
                    <span class="sr-only">{{ config('app.name') }}</span>
                </a>
            </div>

            {{-- Desktop Navigation --}}
            <div class="flex items-center gap-4 lg:gap-8">
                <nav class="hidden md:flex items-center gap-6">
                    <a class="text-[#111811] dark:text-white/80 hover:text-[#13ec13] dark:hover:text-[#13ec13] text-sm font-medium transition-colors {{ request()->routeIs('home') ? 'text-[#13ec13]!' : '' }}" href="{{ route('home') }}" wire:navigate>Inicio</a>
                    <a class="text-[#111811] dark:text-white/80 hover:text-[#13ec13] dark:hover:text-[#13ec13] text-sm font-medium transition-colors {{ request()->routeIs('raffles.*') ? 'text-[#13ec13]!' : '' }}" href="{{ route('raffles.index') }}" wire:navigate>Sorteos</a>
                    <a class="text-[#111811] dark:text-white/80 hover:text-[#13ec13] dark:hover:text-[#13ec13] text-sm font-medium transition-colors" href="{{ route('page.show', 'como-funciona') }}" wire:navigate>Cómo funciona</a>
                    @auth
                        <a class="text-[#111811] dark:text-white/80 hover:text-[#13ec13] dark:hover:text-[#13ec13] text-sm font-medium transition-colors" href="{{ route('orders.index') }}" wire:navigate>Mis Compras</a>
                    @endauth
                </nav>

                {{-- Cart Drawer --}}
                <livewire:components.cart-drawer />

                {{-- Auth Buttons --}}
                @auth
                    <x-public-user-menu />
                @else
                    <a href="{{ route('login') }}" class="hidden sm:flex bg-[#13ec13] text-black px-6 py-2.5 rounded-lg text-sm font-bold tracking-wide hover:opacity-90 transition-all" wire:navigate>
                        Iniciar Sesión
                    </a>
                @endauth

                {{-- Mobile Menu Button --}}
                <button class="md:hidden p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-white/10" onclick="document.getElementById('mobile-menu').classList.toggle('hidden')">
                    <span class="material-symbols-outlined">menu</span>
                </button>
            </div>
        </div>

        {{-- Mobile Menu --}}
        <div id="mobile-menu" class="hidden md:hidden mt-4 pb-4 border-t border-[#dbe6db] dark:border-white/10 pt-4">
            <nav class="flex flex-col gap-4">
                <a class="text-[#111811] dark:text-white/80 hover:text-[#13ec13] text-sm font-medium" href="{{ route('home') }}" wire:navigate>Inicio</a>
                <a class="text-[#111811] dark:text-white/80 hover:text-[#13ec13] text-sm font-medium" href="{{ route('raffles.index') }}" wire:navigate>Sorteos</a>
                <a class="text-[#111811] dark:text-white/80 hover:text-[#13ec13] text-sm font-medium" href="{{ route('page.show', 'como-funciona') }}" wire:navigate>Cómo funciona</a>
                @auth
                    <a class="text-[#111811] dark:text-white/80 hover:text-[#13ec13] text-sm font-medium" href="{{ route('orders.index') }}" wire:navigate>Mis Compras</a>
                @else
                    <a href="{{ route('login') }}" class="bg-[#13ec13] text-black px-6 py-2.5 rounded-lg text-sm font-bold text-center" wire:navigate>Iniciar Sesión</a>
                @endauth
            </nav>
        </div>
    </header>

    {{-- Main Content --}}
    <main class="max-w-[1280px] mx-auto px-4 md:px-10 py-8">
        {{ $slot }}
    </main>

    {{-- Footer --}}
    <footer class="bg-white dark:bg-[#102210] border-t border-[#dbe6db] dark:border-white/10 pt-12 pb-8">
        <div class="max-w-[1280px] mx-auto px-4 md:px-10">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
                {{-- Brand --}}
                <div class="col-span-1 md:col-span-1">
                    <img
                        src="{{ asset('images/logo.webp') }}"
                        alt="{{ config('app.name') }}"
                        class="h-10 w-auto mb-4"
                        loading="lazy"
                    />
                    <p class="text-[#618961] dark:text-white/60 text-sm leading-relaxed">
                        La plataforma de sorteos con premios increíbles. ¡Tu suerte está a un clic de distancia!
                    </p>
                </div>

                {{-- Platform Links --}}
                <div>
                    <h4 class="text-[#111811] dark:text-white font-bold mb-4">Plataforma</h4>
                    <ul class="space-y-2 text-sm text-[#618961] dark:text-white/60">
                        <li><a class="hover:text-[#13ec13]" href="{{ route('page.show', 'como-funciona') }}" wire:navigate>Cómo funciona</a></li>
                        <li><a class="hover:text-[#13ec13]" href="{{ route('raffles.index') }}" wire:navigate>Sorteos activos</a></li>
                    </ul>
                </div>

                {{-- Legal Links --}}
                <div>
                    <h4 class="text-[#111811] dark:text-white font-bold mb-4">Legal</h4>
                    <ul class="space-y-2 text-sm text-[#618961] dark:text-white/60">
                        <li><a class="hover:text-[#13ec13]" href="{{ route('page.show', 'terminos-y-condiciones') }}" wire:navigate>Términos y condiciones</a></li>
                        <li><a class="hover:text-[#13ec13]" href="{{ route('page.show', 'preguntas-frecuentes') }}" wire:navigate>Preguntas frecuentes</a></li>
                    </ul>
                </div>

                {{-- Support --}}
                <div>
                    <h4 class="text-[#111811] dark:text-white font-bold mb-4">Soporte</h4>
                    <a href="https://wa.me/573001234567?text=Hola,%20necesito%20ayuda" target="_blank" class="inline-flex items-center gap-2 bg-[#25D366] text-white px-4 py-2 rounded-lg text-sm font-medium hover:brightness-105 transition-all">
                        <svg class="size-5 fill-current" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        WhatsApp
                    </a>
                </div>
            </div>

            <div class="pt-8 border-t border-[#dbe6db] dark:border-white/10 text-center text-[#618961] dark:text-white/40 text-sm">
                <p>&copy; {{ date('Y') }} {{ config('app.name') }}. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    @fluxScripts
</body>
</html>
