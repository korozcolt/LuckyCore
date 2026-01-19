<?php

declare(strict_types=1);

namespace App\Filament\Resources\RaffleResource\RelationManagers;

use App\Models\Winner;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

/**
 * Relation Manager for Raffle Winners.
 */
class WinnersRelationManager extends RelationManager
{
    protected static string $relationship = 'winners';

    protected static ?string $title = 'Ganadores';

    protected static ?string $modelLabel = 'Ganador';

    protected static ?string $pluralModelLabel = 'Ganadores';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('winner_name')
            ->defaultSort('prize_position')
            ->columns([
                Tables\Columns\TextColumn::make('prize_position')
                    ->label('#')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('prize_name')
                    ->label('Premio')
                    ->searchable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('ticket_number')
                    ->label('Número')
                    ->badge()
                    ->color('success')
                    ->copyable(),

                Tables\Columns\TextColumn::make('winner_name')
                    ->label('Ganador')
                    ->searchable(),

                Tables\Columns\TextColumn::make('winner_email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('prize_value')
                    ->label('Valor')
                    ->money('COP')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_notified')
                    ->label('Notificado')
                    ->boolean(),

                Tables\Columns\IconColumn::make('is_delivered')
                    ->label('Entregado')
                    ->boolean(),

                Tables\Columns\IconColumn::make('is_published')
                    ->label('Publicado')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_notified')
                    ->label('Notificado'),

                Tables\Filters\TernaryFilter::make('is_delivered')
                    ->label('Entregado'),

                Tables\Filters\TernaryFilter::make('is_published')
                    ->label('Publicado'),
            ])
            ->headerActions([
                // Calculate winners action will be on the result page
            ])
            ->actions([
                ViewAction::make()
                    ->infolist([
                        Section::make('Información del Ganador')
                            ->schema([
                                TextEntry::make('winner_name')
                                    ->label('Nombre'),
                                TextEntry::make('winner_email')
                                    ->label('Email'),
                                TextEntry::make('winner_phone')
                                    ->label('Teléfono')
                                    ->placeholder('No registrado'),
                                TextEntry::make('ticket_number')
                                    ->label('Número de ticket')
                                    ->badge()
                                    ->color('success'),
                            ])
                            ->columns(2),

                        Section::make('Premio')
                            ->schema([
                                TextEntry::make('prize_name')
                                    ->label('Premio'),
                                TextEntry::make('prize_value')
                                    ->label('Valor')
                                    ->money('COP'),
                                TextEntry::make('prize_position')
                                    ->label('Posición')
                                    ->badge(),
                            ])
                            ->columns(3),

                        Section::make('Estado')
                            ->schema([
                                IconEntry::make('is_notified')
                                    ->label('Notificado')
                                    ->boolean(),
                                TextEntry::make('notified_at')
                                    ->label('Fecha notificación')
                                    ->dateTime()
                                    ->placeholder('-'),
                                IconEntry::make('is_delivered')
                                    ->label('Entregado')
                                    ->boolean(),
                                TextEntry::make('delivered_at')
                                    ->label('Fecha entrega')
                                    ->dateTime()
                                    ->placeholder('-'),
                                TextEntry::make('delivery_notes')
                                    ->label('Notas de entrega')
                                    ->placeholder('-')
                                    ->columnSpanFull(),
                            ])
                            ->columns(2),
                    ]),

                Action::make('togglePublish')
                    ->label(fn (Winner $record) => $record->is_published ? 'Despublicar' : 'Publicar')
                    ->icon(fn (Winner $record) => $record->is_published ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn (Winner $record) => $record->is_published ? 'warning' : 'success')
                    ->requiresConfirmation()
                    ->action(fn (Winner $record) => $record->is_published ? $record->unpublish() : $record->publish()),

                Action::make('markDelivered')
                    ->label('Marcar entregado')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Winner $record) => ! $record->is_delivered)
                    ->requiresConfirmation()
                    ->form([
                        Textarea::make('notes')
                            ->label('Notas de entrega')
                            ->rows(3)
                            ->placeholder('Detalles de la entrega...'),
                    ])
                    ->action(fn (Winner $record, array $data) => $record->markAsDelivered(
                        auth()->id(),
                        $data['notes'] ?? null
                    )),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('publishAll')
                        ->label('Publicar seleccionados')
                        ->icon('heroicon-o-eye')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each->publish()),

                    BulkAction::make('unpublishAll')
                        ->label('Despublicar seleccionados')
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each->unpublish()),

                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
