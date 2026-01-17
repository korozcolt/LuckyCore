<?php

declare(strict_types=1);

namespace App\Filament\Resources\PaymentGateways\Tables;

use App\Models\PaymentGateway;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentGatewaysTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('provider')
                    ->label('Proveedor')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('display_name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_sandbox')
                    ->label('Sandbox')
                    ->boolean()
                    ->trueIcon('heroicon-o-beaker')
                    ->falseIcon('heroicon-o-server')
                    ->trueColor('warning')
                    ->falseColor('success')
                    ->sortable(),

                Tables\Columns\TextColumn::make('configured')
                    ->label('Configurado')
                    ->badge()
                    ->state(fn (PaymentGateway $record) => $record->isConfigured() ? 'Sí' : 'No')
                    ->color(fn (PaymentGateway $record) => $record->isConfigured() ? 'success' : 'danger'),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Orden')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort_order')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Estado activo'),

                Tables\Filters\TernaryFilter::make('is_sandbox')
                    ->label('Modo sandbox'),
            ])
            ->recordActions([
                Actions\ActionGroup::make([
                    Actions\EditAction::make(),

                    Actions\Action::make('toggle_active')
                        ->label(fn (PaymentGateway $record) => $record->is_active ? 'Desactivar' : 'Activar')
                        ->icon(fn (PaymentGateway $record) => $record->is_active ? 'heroicon-o-pause' : 'heroicon-o-play')
                        ->color(fn (PaymentGateway $record) => $record->is_active ? 'warning' : 'success')
                        ->requiresConfirmation()
                        ->action(fn (PaymentGateway $record) => $record->update(['is_active' => ! $record->is_active])),

                    Actions\Action::make('test_connection')
                        ->label('Probar conexión')
                        ->icon('heroicon-o-signal')
                        ->color('info')
                        ->visible(fn (PaymentGateway $record) => $record->isConfigured())
                        ->action(function (PaymentGateway $record) {
                            // TODO: Implement connection test
                            // For now just show a notification
                        })
                        ->successNotificationTitle('Conexión exitosa'),
                ]),
            ])
            ->toolbarActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->reorderable('sort_order');
    }
}
