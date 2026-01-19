<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\UserRole;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información del usuario')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('email')
                            ->label('Correo electrónico')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                    ]),

                Section::make('Seguridad')
                    ->columns(2)
                    ->schema([
                        TextInput::make('password')
                            ->label('Contraseña')
                            ->password()
                            ->revealable()
                            ->dehydrateStateUsing(fn ($state) => $state ? Hash::make($state) : null)
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn ($context) => $context === 'create')
                            ->rule(Password::default())
                            ->helperText(fn ($context) => $context === 'edit'
                                ? 'Deja en blanco para mantener la contraseña actual.'
                                : 'Mínimo 8 caracteres.'
                            ),

                        TextInput::make('password_confirmation')
                            ->label('Confirmar contraseña')
                            ->password()
                            ->revealable()
                            ->required(fn ($context) => $context === 'create')
                            ->same('password')
                            ->dehydrated(false),
                    ]),

                Section::make('Rol')
                    ->schema([
                        Select::make('role')
                            ->label('Rol del usuario')
                            ->options(function () {
                                $user = Auth::user();
                                $options = [];

                                foreach (UserRole::cases() as $role) {
                                    // Only SuperAdmin can assign SuperAdmin role
                                    if ($role === UserRole::SuperAdmin && ! ($user instanceof User && $user->isSuperAdmin())) {
                                        continue;
                                    }

                                    $options[$role->value] = $role->getLabel();
                                }

                                return $options;
                            })
                            ->required()
                            ->default(UserRole::Customer->value)
                            ->helperText('El rol determina los permisos del usuario en el sistema.'),
                    ]),
            ]);
    }
}
