<?php

declare(strict_types=1);

namespace App\Filament\Resources\CmsPages\Pages;

use App\Filament\Resources\CmsPages\CmsPageResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditCmsPage extends EditRecord
{
    protected static string $resource = CmsPageResource::class;

    protected static ?string $title = 'Editar Página';

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

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['last_edited_by'] = Auth::id();

        return $data;
    }
}
