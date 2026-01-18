<div class="max-w-5xl mx-auto">
    <div class="bg-white dark:bg-white/5 rounded-xl border border-[#dbe6db] dark:border-white/10 p-2">
        <nav class="flex flex-wrap gap-2" aria-label="Configuración">
            <a
                href="{{ route('profile.edit') }}"
                wire:navigate
                class="px-4 py-2 rounded-lg text-sm font-bold transition-colors {{ request()->routeIs('profile.edit') ? 'bg-[#13ec13] text-black' : 'text-[#111811] dark:text-white/80 hover:bg-[#f0f4f0] dark:hover:bg-white/10' }}"
            >
                Perfil
            </a>

            <a
                href="{{ route('user-password.edit') }}"
                wire:navigate
                class="px-4 py-2 rounded-lg text-sm font-bold transition-colors {{ request()->routeIs('user-password.edit') ? 'bg-[#13ec13] text-black' : 'text-[#111811] dark:text-white/80 hover:bg-[#f0f4f0] dark:hover:bg-white/10' }}"
            >
                Contraseña
            </a>

            @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
                <a
                    href="{{ route('two-factor.show') }}"
                    wire:navigate
                    class="px-4 py-2 rounded-lg text-sm font-bold transition-colors {{ request()->routeIs('two-factor.show') ? 'bg-[#13ec13] text-black' : 'text-[#111811] dark:text-white/80 hover:bg-[#f0f4f0] dark:hover:bg-white/10' }}"
                >
                    2FA
                </a>
            @endif

            <a
                href="{{ route('appearance.edit') }}"
                wire:navigate
                class="px-4 py-2 rounded-lg text-sm font-bold transition-colors {{ request()->routeIs('appearance.edit') ? 'bg-[#13ec13] text-black' : 'text-[#111811] dark:text-white/80 hover:bg-[#f0f4f0] dark:hover:bg-white/10' }}"
            >
                Apariencia
            </a>
        </nav>
    </div>

    <section class="mt-6">
        <div class="bg-white dark:bg-white/5 rounded-xl border border-[#dbe6db] dark:border-white/10 p-6">
            <h2 class="text-[#111811] dark:text-white font-bold text-2xl">{{ $heading ?? '' }}</h2>
            <p class="mt-2 text-[#618961] dark:text-white/60">{{ $subheading ?? '' }}</p>

            <div class="mt-8">
                {{ $slot }}
            </div>
        </div>
    </section>
</div>
