<?php

declare(strict_types=1);

namespace App\Filament\Exports;

use App\Models\Ticket;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class TicketExporter extends Exporter
{
    protected static ?string $model = Ticket::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('formatted_code')
                ->label('Ticket'),

            ExportColumn::make('raffle.title')
                ->label('Sorteo'),

            ExportColumn::make('order.order_number')
                ->label('Orden'),

            ExportColumn::make('order.support_code')
                ->label('Código soporte')
                ->enabledByDefault(false),

            ExportColumn::make('order.status')
                ->label('Estado orden')
                ->enabledByDefault(false),

            ExportColumn::make('order.customer_name')
                ->label('Cliente'),

            ExportColumn::make('order.customer_email')
                ->label('Email cliente'),

            ExportColumn::make('user.email')
                ->label('Usuario (email)')
                ->enabledByDefault(false),

            ExportColumn::make('is_winner')
                ->label('Ganador')
                ->enabledByDefault(false),

            ExportColumn::make('prize.name')
                ->label('Premio')
                ->enabledByDefault(false),

            ExportColumn::make('created_at')
                ->label('Creado'),
        ];
    }

    public function getFileName(Export $export): string
    {
        return "tickets-{$export->getKey()}";
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'La exportación de tickets finalizó y se exportaron '.Number::format($export->successful_rows).' '.str('fila')->plural($export->successful_rows).'.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('fila')->plural($failedRowsCount).' fallaron.';
        }

        return $body;
    }
}
