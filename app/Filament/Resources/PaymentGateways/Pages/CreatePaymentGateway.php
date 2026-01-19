<?php

declare(strict_types=1);

namespace App\Filament\Resources\PaymentGateways\Pages;

use App\Filament\Resources\PaymentGateways\PaymentGatewayResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePaymentGateway extends CreateRecord
{
    protected static string $resource = PaymentGatewayResource::class;

    protected static ?string $title = 'Crear Pasarela';

    public function getBreadcrumb(): string
    {
        return 'Crear';
    }
}
