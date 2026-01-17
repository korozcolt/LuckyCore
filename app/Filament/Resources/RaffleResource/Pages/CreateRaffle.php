<?php

namespace App\Filament\Resources\RaffleResource\Pages;

use App\Filament\Resources\RaffleResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRaffle extends CreateRecord
{
    protected static string $resource = RaffleResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
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
