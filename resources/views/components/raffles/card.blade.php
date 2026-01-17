@props(['raffle'])

<div class="group overflow-hidden rounded-lg border border-zinc-200 bg-white transition-shadow hover:shadow-lg dark:border-zinc-700 dark:bg-zinc-900">
    {{-- Image --}}
    <a href="{{ route('raffles.show', $raffle) }}" class="block aspect-video overflow-hidden" wire:navigate>
        @if($raffle->primaryImage)
            <img
                src="{{ $raffle->primaryImage->url }}"
                alt="{{ $raffle->primaryImage->alt_text ?? $raffle->title }}"
                class="h-full w-full object-cover transition-transform group-hover:scale-105"
            >
        @else
            <div class="flex h-full w-full items-center justify-center bg-zinc-100 dark:bg-zinc-800">
                <flux:icon.ticket class="size-12 text-zinc-400" />
            </div>
        @endif
    </a>

    {{-- Content --}}
    <div class="p-4">
        {{-- Status Badge --}}
        <div class="mb-2">
            <flux:badge :color="$raffle->status->getColor()" size="sm">
                {{ $raffle->status->getLabel() }}
            </flux:badge>
        </div>

        {{-- Title --}}
        <a href="{{ route('raffles.show', $raffle) }}" wire:navigate>
            <flux:heading size="sm" class="mb-2 line-clamp-2 hover:text-amber-600">
                {{ $raffle->title }}
            </flux:heading>
        </a>

        {{-- Progress Bar --}}
        <div class="mb-3">
            <div class="mb-1 flex justify-between text-xs">
                <span class="text-zinc-500">{{ $raffle->sold_percentage }}% vendido</span>
                <span class="text-zinc-500">{{ number_format($raffle->available_tickets) }} disponibles</span>
            </div>
            <div class="h-2 overflow-hidden rounded-full bg-zinc-200 dark:bg-zinc-700">
                <div
                    class="h-full rounded-full bg-amber-500 transition-all"
                    style="width: {{ min($raffle->sold_percentage, 100) }}%"
                ></div>
            </div>
        </div>

        {{-- Price and Action --}}
        <div class="flex items-center justify-between">
            <div>
                <flux:text class="text-xs text-zinc-500">Por boleto</flux:text>
                <flux:heading size="sm" class="text-amber-600">
                    {{ $raffle->formatted_price }}
                </flux:heading>
            </div>

            @if($raffle->status->canPurchase())
                <flux:button href="{{ route('raffles.show', $raffle) }}" size="sm" variant="primary" wire:navigate>
                    Ver y Comprar
                </flux:button>
            @elseif($raffle->status === \App\Enums\RaffleStatus::Completed)
                <flux:button href="{{ route('raffles.show', $raffle) }}" size="sm" variant="ghost" wire:navigate>
                    Ver Resultados
                </flux:button>
            @else
                <flux:button href="{{ route('raffles.show', $raffle) }}" size="sm" variant="ghost" wire:navigate>
                    Ver Detalles
                </flux:button>
            @endif
        </div>
    </div>
</div>
