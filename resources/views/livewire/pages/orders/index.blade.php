<div class="container mx-auto px-4 py-8">
    <flux:heading size="xl" class="mb-4">Mis Compras</flux:heading>
    <div class="rounded-lg border border-zinc-200 bg-white p-12 text-center dark:border-zinc-700 dark:bg-zinc-900">
        <flux:icon.shopping-bag class="mx-auto mb-4 size-12 text-zinc-400" />
        <flux:heading size="md" class="mb-2">No tienes compras aún</flux:heading>
        <flux:text class="mb-4">Cuando realices una compra, aparecerá aquí.</flux:text>
        <flux:button href="{{ route('raffles.index') }}" variant="primary">
            Ver Sorteos
        </flux:button>
    </div>
</div>
