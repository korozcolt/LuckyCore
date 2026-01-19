<?php

declare(strict_types=1);

namespace App\Filament\Resources\PaymentGateways\Pages;

use App\Filament\Resources\PaymentGateways\PaymentGatewayResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPaymentGateway extends EditRecord
{
    protected static string $resource = PaymentGatewayResource::class;

    protected static ?string $title = 'Editar Pasarela';

    public function getBreadcrumb(): string
    {
        return 'Editar';
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('Eliminar'),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Expand credentials array for form fields
        $credentials = $data['credentials'] ?? [];

        if ($credentials instanceof \Illuminate\Database\Eloquent\Casts\ArrayObject) {
            $credentials = $credentials->toArray();
        }

        if (is_array($credentials)) {
            $data['credentials'] = $credentials;
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Filter out empty credential values
        if (isset($data['credentials']) && is_array($data['credentials'])) {
            $data['credentials'] = array_filter($data['credentials'], fn ($value) => $value !== null && $value !== '');
        }

        return $data;
    }
}
