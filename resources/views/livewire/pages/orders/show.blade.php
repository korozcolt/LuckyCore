<div>
    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
            <p class="text-green-800 dark:text-green-400 text-sm flex items-center gap-2">
                <span class="material-symbols-outlined">check_circle</span>
                {{ session('success') }}
            </p>
        </div>
    @endif
    @if(session('error'))
        <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
            <p class="text-red-800 dark:text-red-400 text-sm flex items-center gap-2">
                <span class="material-symbols-outlined">error</span>
                {{ session('error') }}
            </p>
        </div>
    @endif
    @if(session('info'))
        <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
            <p class="text-blue-800 dark:text-blue-400 text-sm flex items-center gap-2">
                <span class="material-symbols-outlined">info</span>
                {{ session('info') }}
            </p>
        </div>
    @endif

    {{-- Header --}}
    <div class="mb-8">
        <div class="flex items-center gap-2 text-[#618961] dark:text-white/60 text-sm mb-2">
            @auth
                <a href="{{ route('orders.index') }}" class="hover:text-[#13ec13] transition-colors" wire:navigate>Mis Compras</a>
                <span class="material-symbols-outlined text-base">chevron_right</span>
            @else
                <a href="{{ route('home') }}" class="hover:text-[#13ec13] transition-colors" wire:navigate>Inicio</a>
                <span class="material-symbols-outlined text-base">chevron_right</span>
            @endauth
            <span>Orden #{{ $order->order_number }}</span>
        </div>
        <h1 class="text-[#111811] dark:text-white tracking-tight text-[32px] font-bold leading-tight">Detalle de Orden</h1>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Order Details --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Status Card --}}
            <div class="bg-white dark:bg-white/5 rounded-xl border border-[#dbe6db] dark:border-white/10 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-[#111811] dark:text-white font-bold text-xl">Estado de la Orden</h2>
                    @php
                        $statusColor = match($order->status->value) {
                            'pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
                            'paid' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
                            'failed', 'expired' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
                            'refunded', 'partial_refund' => 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400',
                            default => 'bg-gray-100 text-gray-800',
                        };
                        $statusIcon = match($order->status->value) {
                            'pending' => 'schedule',
                            'paid' => 'check_circle',
                            'failed' => 'cancel',
                            'expired' => 'timer_off',
                            'refunded', 'partial_refund' => 'undo',
                            default => 'info',
                        };
                    @endphp
                    <span class="px-3 py-1 rounded-full text-sm font-semibold flex items-center gap-1 {{ $statusColor }}">
                        <span class="material-symbols-outlined text-base">{{ $statusIcon }}</span>
                        {{ $order->status->getLabel() }}
                    </span>
                </div>

                @if($order->status->value === 'pending')
                    <div class="p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                        <p class="text-yellow-800 dark:text-yellow-400 text-sm flex items-center gap-2">
                            <span class="material-symbols-outlined">info</span>
                            Tu pago está pendiente. Haz clic en el botón para completar tu compra.
                        </p>
                        <a href="{{ route('payment', $order->ulid) }}" class="mt-3 px-4 py-2 bg-[#13ec13] hover:bg-[#13ec13]/90 text-white font-semibold rounded-lg transition-colors inline-flex items-center gap-2" wire:navigate>
                            <span class="material-symbols-outlined">payment</span>
                            Completar Pago
                        </a>
                    </div>
                @elseif($order->status->value === 'paid')
                    <div class="p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                        <p class="text-green-800 dark:text-green-400 text-sm flex items-center gap-2">
                            <span class="material-symbols-outlined">check_circle</span>
                            Tu pago fue procesado exitosamente. Tus boletos han sido asignados.
                        </p>
                    </div>
                @elseif(in_array($order->status->value, ['failed', 'expired']))
                    <div class="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                        <p class="text-red-800 dark:text-red-400 text-sm flex items-center gap-2">
                            <span class="material-symbols-outlined">error</span>
                            {{ $order->status->value === 'expired' ? 'El tiempo para pagar expiró.' : 'Hubo un problema con tu pago.' }}
                        </p>
                        @if($order->canRetry())
                            <a href="{{ route('payment', $order->ulid) }}" class="mt-3 px-4 py-2 bg-[#13ec13] hover:bg-[#13ec13]/90 text-white font-semibold rounded-lg transition-colors inline-flex items-center gap-2" wire:navigate>
                                <span class="material-symbols-outlined">refresh</span>
                                Reintentar Pago
                            </a>
                        @endif
                    </div>
                @endif
            </div>

            {{-- Order Items --}}
            <div class="bg-white dark:bg-white/5 rounded-xl border border-[#dbe6db] dark:border-white/10 p-6">
                <h2 class="text-[#111811] dark:text-white font-bold text-xl mb-6 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#13ec13]">confirmation_number</span>
                    Boletos Comprados
                </h2>

                <div class="space-y-4">
                    @foreach($order->items as $item)
                        <div class="flex items-center gap-4 pb-4 border-b border-[#dbe6db] dark:border-white/10 last:border-0 last:pb-0">
                            <div class="w-20 h-20 rounded-lg overflow-hidden flex-shrink-0">
                                <div class="w-full h-full bg-center bg-cover" style="background-image: url('{{ $item->raffle?->primaryImage?->url ?? 'https://placehold.co/80x80/1a2e1a/13ec13?text=' . urlencode(substr($item->raffle_title, 0, 2)) }}');"></div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="text-[#111811] dark:text-white font-semibold">{{ $item->raffle_title }}</h3>
                                <p class="text-[#618961] dark:text-white/60 text-sm">
                                    {{ $item->quantity }} boletos x {{ $item->formatted_unit_price }}
                                    @if($item->package)
                                        <span class="text-[#13ec13]">({{ $item->package->name }})</span>
                                    @endif
                                </p>
                                @if($order->isPaid())
                                    <p class="text-[#13ec13] text-sm mt-1 flex items-center gap-1">
                                        <span class="material-symbols-outlined text-sm">check</span>
                                        {{ $item->tickets_assigned }} boletos asignados
                                    </p>
                                @endif
                            </div>
                            <div class="text-right">
                                <p class="text-[#111811] dark:text-white font-bold">{{ $item->formatted_subtotal }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Tickets (if paid) --}}
            @if($order->isPaid() && $order->tickets->count() > 0)
                <div class="bg-white dark:bg-white/5 rounded-xl border border-[#dbe6db] dark:border-white/10 p-6">
                    <h2 class="text-[#111811] dark:text-white font-bold text-xl mb-6 flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#13ec13]">receipt</span>
                        Tus Números de Boleto
                    </h2>

                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3">
                        @foreach($order->tickets as $ticket)
                            <div class="p-3 bg-[#f0f4f0] dark:bg-white/5 rounded-lg text-center {{ $ticket->is_winner ? 'ring-2 ring-[#13ec13] bg-[#13ec13]/10' : '' }}">
                                <p class="text-[#111811] dark:text-white font-mono font-bold text-lg">{{ $ticket->formatted_code }}</p>
                                @if($ticket->is_winner)
                                    <p class="text-[#13ec13] text-xs font-semibold mt-1 flex items-center justify-center gap-1">
                                        <span class="material-symbols-outlined text-sm">emoji_events</span>
                                        Ganador
                                    </p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        {{-- Order Summary Sidebar --}}
        <div class="lg:col-span-1">
            <div class="bg-white dark:bg-white/5 rounded-xl border border-[#dbe6db] dark:border-white/10 p-6 sticky top-24">
                <h2 class="text-[#111811] dark:text-white font-bold text-xl mb-6">Resumen</h2>

                {{-- Order Info --}}
                <div class="space-y-3 mb-6 pb-6 border-b border-[#dbe6db] dark:border-white/10">
                    <div class="flex justify-between text-sm">
                        <span class="text-[#618961] dark:text-white/60">Número de orden</span>
                        <span class="text-[#111811] dark:text-white font-mono font-medium">{{ $order->order_number }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-[#618961] dark:text-white/60">Fecha</span>
                        <span class="text-[#111811] dark:text-white font-medium">{{ $order->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-[#618961] dark:text-white/60">Código de soporte</span>
                        <span class="text-[#13ec13] font-mono font-bold">{{ $order->support_code }}</span>
                    </div>
                </div>

                {{-- Totals --}}
                <div class="space-y-3 mb-6">
                    <div class="flex justify-between text-sm">
                        <span class="text-[#618961] dark:text-white/60">Subtotal</span>
                        <span class="text-[#111811] dark:text-white font-medium">{{ $order->formatted_total }}</span>
                    </div>
                </div>

                <div class="border-t border-[#dbe6db] dark:border-white/10 pt-4 mb-6">
                    <div class="flex justify-between items-center">
                        <span class="text-[#111811] dark:text-white font-bold text-lg">Total</span>
                        <span class="text-[#13ec13] font-bold text-2xl">{{ $order->formatted_total }}</span>
                    </div>
                    <p class="text-[#618961] dark:text-white/60 text-sm mt-1">
                        {{ $order->total_tickets }} boletos
                    </p>
                </div>

                {{-- Support --}}
                <a
                    href="https://wa.me/573001234567?text={{ urlencode('Hola, necesito ayuda con mi orden ' . $order->support_code) }}"
                    target="_blank"
                    class="w-full py-3 border border-[#dbe6db] dark:border-white/10 text-[#618961] dark:text-white/60 font-semibold rounded-lg hover:bg-[#f0f4f0] dark:hover:bg-white/5 transition-colors flex items-center justify-center gap-2"
                >
                    <span class="material-symbols-outlined text-lg text-green-500">chat</span>
                    Soporte WhatsApp
                </a>
            </div>
        </div>
    </div>
</div>
