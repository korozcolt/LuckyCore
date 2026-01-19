<?php

declare(strict_types=1);

namespace App\Filament\Resources\Tickets\Tables;

use App\Enums\OrderStatus;
use App\Filament\Exports\TicketExporter;
use App\Models\Ticket;
use Filament\Actions\ExportAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TicketsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Ticket')
                    ->state(fn (Ticket $record): string => $record->formatted_code)
                    ->badge()
                    ->color(fn (Ticket $record): string => $record->is_winner ? 'success' : 'gray')
                    ->copyable()
                    ->copyMessage('Ticket copiado')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('raffle.title')
                    ->label('Sorteo')
                    ->searchable()
                    ->sortable()
                    ->limit(40),

                TextColumn::make('order.order_number')
                    ->label('# Orden')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Número copiado')
                    ->weight('bold'),

                TextColumn::make('order.status')
                    ->label('Estado Orden')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('order.customer_email')
                    ->label('Email Cliente')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('order.support_code')
                    ->label('Código Soporte')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Código copiado')
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('is_winner')
                    ->label('Ganador')
                    ->boolean()
                    ->alignCenter(),

                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('raffle_id')
                    ->label('Sorteo')
                    ->relationship('raffle', 'title')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('order_status')
                    ->label('Estado Orden')
                    ->options(OrderStatus::class)
                    ->query(function (Builder $query, array $data): Builder {
                        $value = $data['value'] ?? null;

                        if (! filled($value)) {
                            return $query;
                        }

                        return $query->whereHas('order', fn (Builder $orderQuery) => $orderQuery->where('status', $value));
                    }),

                TernaryFilter::make('is_winner')
                    ->label('Ganador'),

                TernaryFilter::make('has_user')
                    ->label('Con usuario')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('user_id'),
                        false: fn (Builder $query) => $query->whereNull('user_id'),
                        blank: fn (Builder $query) => $query,
                    ),
            ])
            ->headerActions([
                ExportAction::make()
                    ->label('Exportar')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->exporter(TicketExporter::class)
                    ->enableVisibleTableColumnsByDefault()
                    ->columnMappingColumns(2),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
