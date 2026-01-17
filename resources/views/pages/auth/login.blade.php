<x-layouts.auth>
    <div class="bg-[#1a2e1a] rounded-2xl p-8 border border-[#2a442a] shadow-2xl">
        {{-- Header --}}
        <div class="text-center mb-8">
            <h2 class="text-2xl font-bold text-white mb-2">Iniciar Sesión</h2>
            <p class="text-white/60">Ingresa tus credenciales para continuar</p>
        </div>

        {{-- Session Status --}}
        @if (session('status'))
            <div class="mb-6 p-4 bg-[#13ec13]/10 border border-[#13ec13]/30 rounded-lg text-center">
                <p class="text-[#13ec13] text-sm">{{ session('status') }}</p>
            </div>
        @endif

        <form method="POST" action="{{ route('login.store') }}" class="space-y-5">
            @csrf

            {{-- Email Address --}}
            <div>
                <label for="email" class="block text-sm font-medium text-white mb-2">Correo electrónico</label>
                <input
                    id="email"
                    name="email"
                    type="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
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
                <div class="flex items-center justify-between mb-2">
                    <label for="password" class="block text-sm font-medium text-white">Contraseña</label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-sm text-[#13ec13] hover:underline" wire:navigate>
                            ¿Olvidaste tu contraseña?
                        </a>
                    @endif
                </div>
                <input
                    id="password"
                    name="password"
                    type="password"
                    required
                    autocomplete="current-password"
                    placeholder="Tu contraseña"
                    class="w-full px-4 py-3 bg-[#102210] border border-[#2a442a] rounded-lg text-white placeholder-white/40 focus:outline-none focus:ring-2 focus:ring-[#13ec13] focus:border-transparent transition-all"
                >
                @error('password')
                    <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Remember Me --}}
            <div class="flex items-center">
                <input
                    id="remember"
                    name="remember"
                    type="checkbox"
                    {{ old('remember') ? 'checked' : '' }}
                    class="size-4 rounded border-[#2a442a] bg-[#102210] text-[#13ec13] focus:ring-[#13ec13] focus:ring-offset-0"
                >
                <label for="remember" class="ml-2 text-sm text-white/70">Recordarme</label>
            </div>

            {{-- Submit Button --}}
            <button
                type="submit"
                class="w-full py-3 bg-[#13ec13] hover:brightness-110 text-black font-bold rounded-lg transition-all flex items-center justify-center gap-2"
            >
                Iniciar Sesión
            </button>
        </form>

        {{-- Register Link --}}
        @if (Route::has('register'))
            <div class="mt-8 pt-6 border-t border-[#2a442a] text-center">
                <p class="text-white/60">
                    ¿No tienes una cuenta?
                    <a href="{{ route('register') }}" class="text-[#13ec13] font-medium hover:underline" wire:navigate>
                        Regístrate aquí
                    </a>
                </p>
            </div>
        @endif
    </div>
</x-layouts.auth>
