<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    @include('partials.head')
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
    </style>
</head>
<body class="min-h-screen bg-[#102210] text-white">
    <div class="min-h-screen flex flex-col">
        {{-- Header --}}
        <header class="py-6 px-4">
            <div class="max-w-md mx-auto">
                <a href="{{ route('home') }}" class="flex items-center justify-center gap-2" wire:navigate>
                    <img
                        src="{{ asset('images/logo.webp') }}"
                        alt="{{ config('app.name') }}"
                        class="h-10 w-auto"
                        loading="eager"
                    />
                    <span class="sr-only">{{ config('app.name') }}</span>
                </a>
            </div>
        </header>

        {{-- Main Content --}}
        <main class="flex-1 flex items-center justify-center px-4 py-8">
            <div class="w-full max-w-md">
                {{ $slot }}
            </div>
        </main>

        {{-- Footer --}}
        <footer class="py-6 px-4 text-center">
            <p class="text-sm text-white/40">&copy; {{ date('Y') }} {{ config('app.name') }}. Todos los derechos reservados.</p>
        </footer>
    </div>

    @fluxScripts
</body>
</html>
