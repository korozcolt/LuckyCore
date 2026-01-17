<?php

declare(strict_types=1);

namespace App\Filament\Resources\PaymentGateways;

use App\Filament\Resources\PaymentGateways\Pages\CreatePaymentGateway;
use App\Filament\Resources\PaymentGateways\Pages\EditPaymentGateway;
use App\Filament\Resources\PaymentGateways\Pages\ListPaymentGateways;
use App\Filament\Resources\PaymentGateways\Schemas\PaymentGatewayForm;
use App\Filament\Resources\PaymentGateways\Tables\PaymentGatewaysTable;
use App\Models\PaymentGateway;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

/**
 * Filament Resource for Payment Gateways.
 *
 * @see ALCANCE.md §3 - Integración con pasarelas: Wompi, MercadoPago, ePayco
 */
class PaymentGatewayResource extends Resource
{
    protected static ?string $model = PaymentGateway::class;

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-credit-card';
    }

    public static function getModelLabel(): string
    {
        return 'Pasarela de Pago';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Pasarelas de Pago';
    }

    public static function getNavigationSort(): ?int
    {
        return 50;
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Configuración';
    }

    public static function form(Schema $schema): Schema
    {
        return PaymentGatewayForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PaymentGatewaysTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPaymentGateways::route('/'),
            'create' => CreatePaymentGateway::route('/create'),
            'edit' => EditPaymentGateway::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('is_active', true)->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
}
