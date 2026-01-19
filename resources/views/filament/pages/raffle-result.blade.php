<div class="space-y-6">
    {{-- Result Info --}}
    <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Datos del Sorteo</h3>
        <dl class="grid grid-cols-3 gap-4">
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Lotería</dt>
                <dd class="text-sm text-gray-900 dark:text-white">{{ $result->lottery_name ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Número Ganador</dt>
                <dd class="text-xl font-bold text-success-600 dark:text-success-400">{{ $result->lottery_number }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Fecha</dt>
                <dd class="text-sm text-gray-900 dark:text-white">{{ $result->lottery_date?->format('d/m/Y') }}</dd>
            </div>
        </dl>
    </div>

    {{-- Winners List --}}
    <div>
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">
            Ganadores ({{ $winners->count() }})
        </h3>

        @if($winners->isEmpty())
            <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                <x-heroicon-o-trophy class="w-12 h-12 mx-auto mb-2 opacity-50" />
                <p>No se encontraron ganadores para este sorteo.</p>
            </div>
        @else
            <div class="space-y-3">
                @foreach($winners as $winner)
                    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <span class="flex items-center justify-center w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-900 text-primary-600 dark:text-primary-400 font-bold text-sm">
                                    {{ $winner->prize_position }}
                                </span>
                                <div>
                                    <p class="font-semibold text-gray-900 dark:text-white">{{ $winner->prize_name }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        Ticket: <span class="font-mono text-success-600 dark:text-success-400">{{ $winner->ticket_number }}</span>
                                    </p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-gray-900 dark:text-white">{{ $winner->winner_name }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $winner->formatted_prize_value }}</p>
                            </div>
                        </div>

                        {{-- Status badges --}}
                        <div class="flex gap-2 mt-3">
                            @if($winner->is_notified)
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                    <x-heroicon-s-bell class="w-3 h-3 mr-1" /> Notificado
                                </span>
                            @endif
                            @if($winner->is_delivered)
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                    <x-heroicon-s-check-circle class="w-3 h-3 mr-1" /> Entregado
                                </span>
                            @endif
                            @if($winner->is_published)
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                                    <x-heroicon-s-eye class="w-3 h-3 mr-1" /> Publicado
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                    <x-heroicon-s-eye-slash class="w-3 h-3 mr-1" /> No publicado
                                </span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
