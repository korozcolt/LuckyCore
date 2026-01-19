<?php

declare(strict_types=1);

namespace App\Filament\Resources\WinnerTestimonialResource\Pages;

use App\Filament\Resources\WinnerTestimonialResource;
use Filament\Resources\Pages\ListRecords;

class ListWinnerTestimonials extends ListRecords
{
    protected static string $resource = WinnerTestimonialResource::class;

    protected static ?string $title = 'Testimonios de Ganadores';

    protected function getHeaderActions(): array
    {
        return [];
    }
}
