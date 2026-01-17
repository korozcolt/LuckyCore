<x-layouts.auth>
    <div class="bg-[#1a2e1a] rounded-2xl p-8 border border-[#2a442a] shadow-2xl">
        {{-- Header --}}
        <div class="text-center mb-8">
            <h2 class="text-2xl font-bold text-white mb-2">Crear Cuenta</h2>
            <p class="text-white/60">Completa tus datos para registrarte</p>
        </div>

        {{-- Session Status --}}
        @if (session('status'))
            <div class="mb-6 p-4 bg-[#13ec13]/10 border border-[#13ec13]/30 rounded-lg text-center">
                <p class="text-[#13ec13] text-sm">{{ session('status') }}</p>
            </div>
        @endif

        <form method="POST" action="{{ route('register.store') }}" class="space-y-5">
            @csrf

            {{-- Name --}}
            <div>
                <label for="name" class="block text-sm font-medium text-white mb-2">Nombre completo</label>
                <input
                    id="name"
                    name="name"
                    type="text"
                    value="{{ old('name') }}"
                    required
                    autofocus
                    autocomplete="name"
                    placeholder="Tu nombre"
                    class="w-full px-4 py-3 bg-[#102210] border border-[#2a442a] rounded-lg text-white placeholder-white/40 focus:outline-none focus:ring-2 focus:ring-[#13ec13] focus:border-transparent transition-all"
                >
                @error('name')
                    <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Email Address --}}
            <div>
                <label for="email" class="block text-sm font-medium text-white mb-2">Correo electrónico</label>
                <input
                    id="email"
                    name="email"
                    type="email"
                    value="{{ old('email') }}"
                    required
                    autocomplete="email"
                    placeholder="tu@email.com"
                    class="w-full px-4 py-3 bg-[#102210] border border-[#2a442a] rounded-lg text-white placeholder-white/40 focus:outline-none focus:ring-2 focus:ring-[#13ec13] focus:border-transparent transition-all"
                >
                @error('email')
                    <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Password --}}
            <div>
                <label for="password" class="block text-sm font-medium text-white mb-2">Contraseña</label>
                <input
                    id="password"
                    name="password"
                    type="password"
                    required
                    autocomplete="new-password"
                    placeholder="Mínimo 8 caracteres"
                    class="w-full px-4 py-3 bg-[#102210] border border-[#2a442a] rounded-lg text-white placeholder-white/40 focus:outline-none focus:ring-2 focus:ring-[#13ec13] focus:border-transparent transition-all"
                >
                @error('password')
                    <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Confirm Password --}}
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-white mb-2">Confirmar contraseña</label>
                <input
                    id="password_confirmation"
                    name="password_confirmation"
                    type="password"
                    required
                    autocomplete="new-password"
                    placeholder="Repite tu contraseña"
                    class="w-full px-4 py-3 bg-[#102210] border border-[#2a442a] rounded-lg text-white placeholder-white/40 focus:outline-none focus:ring-2 focus:ring-[#13ec13] focus:border-transparent transition-all"
                >
            </div>

            {{-- Submit Button --}}
            <button
                type="submit"
                class="w-full py-3 bg-[#13ec13] hover:brightness-110 text-black font-bold rounded-lg transition-all"
            >
                Crear Cuenta
            </button>
        </form>

        {{-- Login Link --}}
        <div class="mt-8 pt-6 border-t border-[#2a442a] text-center">
            <p class="text-white/60">
                ¿Ya tienes una cuenta?
                <a href="{{ route('login') }}" class="text-[#13ec13] font-medium hover:underline" wire:navigate>
                    Inicia sesión
                </a>
            </p>
        </div>
    </div>
</x-layouts.auth>
