<?php

declare(strict_types=1);

namespace App\Filament\Resources\Orders\Schemas;

use App\Enums\OrderStatus;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;

class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Order')
                    ->tabs([
                        // Tab 1: Información general
                        Tabs\Tab::make('Información')
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                Grid::make(3)->schema([
                                    // Order Info
                                    Section::make('Datos de la orden')
                                        ->columnSpan(2)
                                        ->columns(2)
                                        ->schema([
                                            TextEntry::make('order_number')
                                                ->label('Número de orden')
                                                ->weight('bold')
                                                ->copyable(),

                                            TextEntry::make('status')
                                                ->label('Estado')
                                                ->badge()
                                                ->color(fn (OrderStatus $state): string => match ($state) {
                                                    OrderStatus::Pending => 'warning',
                                                    OrderStatus::Paid => 'success',
                                                    OrderStatus::Failed, OrderStatus::Expired => 'danger',
                                                    default => 'gray',
                                                }),

                                            TextEntry::make('support_code')
                                                ->label('Código de soporte')
                                                ->copyable()
                                                ->weight('bold'),

                                            TextEntry::make('created_at')
                                                ->label('Fecha de creación')
                                                ->dateTime('d/m/Y H:i'),

                                            TextEntry::make('paid_at')
                                                ->label('Fecha de pago')
                                                ->dateTime('d/m/Y H:i')
                                                ->placeholder('No pagada'),
                                        ]),

                                    // Customer Info
                                    Section::make('Cliente')
                                        ->columnSpan(1)
                                        ->schema([
                                            TextEntry::make('customer_name')
                                                ->label('Nombre'),

                                            TextEntry::make('customer_email')
                                                ->label('Email')
                                                ->copyable(),

                                            TextEntry::make('customer_phone')
                                                ->label('Teléfono')
                                                ->placeholder('No proporcionado'),

                                            TextEntry::make('user.name')
                                                ->label('Usuario registrado')
                                                ->placeholder('Invitado'),
                                        ]),
                                ]),

                                // Totals
                                Section::make('Totales')
                                    ->columns(3)
                                    ->schema([
                                        TextEntry::make('subtotal')
                                            ->label('Subtotal')
                                            ->money('COP', divideBy: 100),

                                        TextEntry::make('total')
                                            ->label('Total')
                                            ->money('COP', divideBy: 100)
                                            ->weight('bold')
                                            ->size('lg'),

                                        TextEntry::make('total_tickets')
                                            ->label('Total boletos'),
                                    ]),
                            ]),

                        // Tab 2: Items
                        Tabs\Tab::make('Items')
                            ->icon('heroicon-o-shopping-cart')
                            ->badge(fn ($record) => $record->items->count())
                            ->schema([
                                RepeatableEntry::make('items')
                                    ->label('')
                                    ->columns(4)
                                    ->schema([
                                        TextEntry::make('raffle_title')
                                            ->label('Sorteo'),

                                        TextEntry::make('quantity')
                                            ->label('Cantidad'),

                                        TextEntry::make('unit_price')
                                            ->label('Precio unit.')
                                            ->money('COP', divideBy: 100),

                                        TextEntry::make('subtotal')
                                            ->label('Subtotal')
                                            ->money('COP', divideBy: 100),
                                    ]),
                            ]),

                        // Tab 3: Transacciones de pago
                        Tabs\Tab::make('Transacciones')
                            ->icon('heroicon-o-credit-card')
                            ->badge(fn ($record) => $record->transactions->count())
                            ->schema([
                                RepeatableEntry::make('transactions')
                                    ->label('')
                                    ->columns(5)
                                    ->schema([
                                        TextEntry::make('provider')
                                            ->label('Proveedor')
                                            ->badge(),

                                        TextEntry::make('status')
                                            ->label('Estado')
                                            ->badge()
                                            ->color(fn ($state) => match ($state?->value ?? $state) {
                                                'approved' => 'success',
                                                'pending', 'processing' => 'warning',
                                                'rejected', 'expired', 'voided' => 'danger',
                                                default => 'gray',
                                            }),

                                        TextEntry::make('amount')
                                            ->label('Monto')
                                            ->money('COP', divideBy: 100),

                                        TextEntry::make('provider_transaction_id')
                                            ->label('ID Proveedor')
                                            ->placeholder('-')
                                            ->copyable(),

                                        TextEntry::make('initiated_at')
                                            ->label('Fecha')
                                            ->dateTime('d/m/Y H:i'),
                                    ]),
                            ]),

                        // Tab 4: Timeline de eventos
                        Tabs\Tab::make('Timeline')
                            ->icon('heroicon-o-clock')
                            ->badge(fn ($record) => $record->events->count())
                            ->schema([
                                RepeatableEntry::make('events')
                                    ->label('')
                                    ->schema([
                                        Grid::make(4)->schema([
                                            TextEntry::make('created_at')
                                                ->label('Fecha')
                                                ->dateTime('d/m/Y H:i:s'),

                                            TextEntry::make('event_label')
                                                ->label('Evento')
                                                ->badge()
                                                ->color(fn ($record) => $record->is_error ? 'danger' : 'info'),

                                            TextEntry::make('description')
                                                ->label('Descripción')
                                                ->columnSpan(2)
                                                ->placeholder('-'),
                                        ]),

                                        Group::make([
                                            TextEntry::make('error_message')
                                                ->label('Error')
                                                ->color('danger')
                                                ->visible(fn ($record) => $record->is_error),

                                            TextEntry::make('metadata')
                                                ->label('Metadata')
                                                ->formatStateUsing(fn ($state) => $state ? json_encode($state, JSON_PRETTY_PRINT) : null)
                                                ->visible(fn ($state) => ! empty($state)),
                                        ])->columnSpanFull(),
                                    ]),
                            ]),

                        // Tab 5: Info técnica
                        Tabs\Tab::make('Info técnica')
                            ->icon('heroicon-o-cog-6-tooth')
                            ->schema([
                                Section::make('Información de la sesión')
                                    ->columns(2)
                                    ->schema([
                                        TextEntry::make('ip_address')
                                            ->label('IP'),

                                        TextEntry::make('user_agent')
                                            ->label('User Agent')
                                            ->wrap(),

                                        TextEntry::make('ulid')
                                            ->label('ULID')
                                            ->copyable(),

                                        TextEntry::make('correlation_id')
                                            ->label('Correlation ID')
                                            ->copyable(),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
