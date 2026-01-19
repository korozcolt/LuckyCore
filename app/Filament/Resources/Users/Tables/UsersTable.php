<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Tables;

use App\Enums\UserRole;
use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('email')
                    ->label('Correo')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Correo copiado'),

                TextColumn::make('roles.name')
                    ->label('Rol')
                    ->badge()
                    ->formatStateUsing(fn ($state) => UserRole::tryFrom($state)?->getLabel() ?? $state)
                    ->color(fn ($state) => UserRole::tryFrom($state)?->getColor() ?? 'gray'),

                TextColumn::make('orders_count')
                    ->label('Órdenes')
                    ->counts('orders')
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('created_at')
                    ->label('Registro')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Última actualización')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->label('Rol')
                    ->options(
                        collect(UserRole::cases())
                            ->mapWithKeys(fn ($role) => [$role->value => $role->getLabel()])
                            ->toArray()
                    )
                    ->query(function ($query, array $data) {
                        if (filled($data['value'])) {
                            $query->whereHas('roles', fn ($q) => $q->where('name', $data['value']));
                        }
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->visible(function ($record) {
                        $user = Auth::user();

                        // Only SuperAdmin can delete users
                        if (! $user instanceof User || ! $user->isSuperAdmin()) {
                            return false;
                        }

                        // Cannot delete yourself
                        return $record->id !== $user->id;
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn () => Auth::user() instanceof User && Auth::user()->isSuperAdmin()),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped();
    }
}
