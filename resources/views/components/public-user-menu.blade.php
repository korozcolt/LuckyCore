<flux:dropdown position="bottom" align="end">
    <button
        type="button"
        class="h-10 w-10 rounded-full bg-[#13ec13]/20 flex items-center justify-center border border-[#13ec13]/30 hover:bg-[#13ec13]/25 transition-colors"
        aria-label="Perfil"
        data-test="public-user-menu-button"
    >
        <span class="material-symbols-outlined text-[#13ec13]">person</span>
    </button>

    <flux:menu>
        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
            <flux:avatar
                :name="auth()->user()->name"
                :initials="auth()->user()->initials()"
            />
            <div class="grid flex-1 text-start text-sm leading-tight">
                <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
            </div>
        </div>

        <flux:menu.separator />

        <flux:menu.item :href="route('orders.index')" wire:navigate>
            Mis Compras
        </flux:menu.item>

        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
            Configuración
        </flux:menu.item>

        <flux:menu.separator />

        <form method="POST" action="{{ route('logout') }}" class="w-full">
            @csrf
            <flux:menu.item
                as="button"
                type="submit"
                icon="arrow-right-start-on-rectangle"
                class="w-full cursor-pointer"
                data-test="logout-button"
            >
                Cerrar sesión
            </flux:menu.item>
        </form>
    </flux:menu>
</flux:dropdown>
