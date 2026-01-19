<?php

declare(strict_types=1);

namespace App\Filament\Resources\Tickets\Schemas;

use App\Models\Ticket;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TicketInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Ticket')
                    ->schema([
                        TextEntry::make('formatted_code')
                            ->label('Número')
                            ->badge()
                            ->copyable()
                            ->copyMessage('Ticket copiado')
                            ->state(fn (Ticket $record): string => $record->formatted_code),

                        TextEntry::make('raffle.title')
                            ->label('Sorteo'),

                        TextEntry::make('order.order_number')
                            ->label('# Orden')
                            ->copyable()
                            ->copyMessage('Número copiado'),

                        TextEntry::make('order.support_code')
                            ->label('Código soporte')
                            ->copyable()
                            ->copyMessage('Código copiado'),

                        IconEntry::make('is_winner')
                            ->label('Ganador')
                            ->boolean(),

                        TextEntry::make('prize.name')
                            ->label('Premio')
                            ->placeholder('-'),
                    ])
                    ->columns(2),

                Section::make('Cliente')
                    ->schema([
                        TextEntry::make('customer_name')
                            ->label('Nombre')
                            ->state(fn (Ticket $record): string => $record->user?->name ?? $record->order?->customer_name ?? 'Invitado'),

                        TextEntry::make('customer_email')
                            ->label('Email')
                            ->state(fn (Ticket $record): string => $record->user?->email ?? $record->order?->customer_email ?? '-'),
                    ])
                    ->columns(2),

                Section::make('Fechas')
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Creado')
                            ->dateTime('d/m/Y H:i'),

                        TextEntry::make('won_at')
                            ->label('Ganó en')
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('-'),
                    ])
                    ->columns(2),
            ]);
    }
}
