<?php

declare(strict_types=1);

namespace App\Filament\Resources\WinnerTestimonialResource\Pages;

use App\Filament\Resources\WinnerTestimonialResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWinnerTestimonial extends EditRecord
{
    protected static string $resource = WinnerTestimonialResource::class;

    protected static ?string $title = 'Editar Testimonio';

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
