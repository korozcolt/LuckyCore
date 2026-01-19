<?php

declare(strict_types=1);

namespace App\Filament\Resources\CmsPages\Pages;

use App\Filament\Resources\CmsPages\CmsPageResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateCmsPage extends CreateRecord
{
    protected static string $resource = CmsPageResource::class;

    protected static ?string $title = 'Crear Página';

    public function getBreadcrumb(): string
    {
        return 'Crear';
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['last_edited_by'] = Auth::id();

        return $data;
    }
}
