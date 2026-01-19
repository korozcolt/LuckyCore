<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Pages;

use App\Enums\UserRole;
use App\Filament\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected static ?string $title = 'Editar Usuario';

    public function getBreadcrumb(): string
    {
        return 'Editar';
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('Eliminar')
                ->visible(function () {
                    $user = Auth::user();

                    // Only SuperAdmin can delete
                    if (! $user?->isSuperAdmin()) {
                        return false;
                    }

                    // Cannot delete yourself
                    return $this->record->id !== $user->id;
                }),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Get the user's current role
        $data['role'] = $this->record->roles->first()?->name ?? UserRole::Customer->value;

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Extract role from form data
        $role = $data['role'] ?? null;
        unset($data['role']);

        // Store role temporarily to assign after save
        if ($role) {
            $this->roleToAssign = $role;
        }

        return $data;
    }

    protected function afterSave(): void
    {
        // Sync the role
        if (isset($this->roleToAssign)) {
            $this->record->syncRoles([$this->roleToAssign]);
        }
    }

    protected string $roleToAssign;
}
