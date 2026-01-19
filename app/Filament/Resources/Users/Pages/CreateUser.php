<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Pages;

use App\Enums\UserRole;
use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected static ?string $title = 'Crear Usuario';

    public function getBreadcrumb(): string
    {
        return 'Crear';
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Extract role from form data
        $role = $data['role'] ?? UserRole::Customer->value;
        unset($data['role']);

        // Store role temporarily to assign after creation
        $this->roleToAssign = $role;

        return $data;
    }

    protected function afterCreate(): void
    {
        // Assign the role to the created user
        if (isset($this->roleToAssign)) {
            $this->record->syncRoles([$this->roleToAssign]);
        }
    }

    protected string $roleToAssign;
}
