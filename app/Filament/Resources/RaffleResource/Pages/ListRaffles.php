<?php

declare(strict_types=1);

namespace App\Filament\Resources\RaffleResource\Pages;

use App\Filament\Resources\RaffleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRaffles extends ListRecords
{
    protected static string $resource = RaffleResource::class;

    protected static ?string $title = 'Sorteos';

    public function getBreadcrumb(): string
    {
        return 'Listado';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Nuevo Sorteo'),
        ];
    }
}
