<?php

declare(strict_types=1);

namespace App\Filament\Resources\RaffleResource\Pages;

use App\Filament\Resources\RaffleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRaffle extends EditRecord
{
    protected static string $resource = RaffleResource::class;

    protected static ?string $title = 'Editar Sorteo';

    public function getBreadcrumb(): string
    {
        return 'Editar';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->label('Ver'),
            Actions\DeleteAction::make()
                ->label('Eliminar'),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Convert price from storage format (centavos) to display format (pesos)
        if (isset($data['ticket_price'])) {
            $data['ticket_price'] = $data['ticket_price'] / 100;
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Convert price from display format (pesos) to storage format (centavos)
        if (isset($data['ticket_price'])) {
            $data['ticket_price'] = (int) ($data['ticket_price'] * 100);
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
