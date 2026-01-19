<?php

declare(strict_types=1);

namespace App\Filament\Resources\CmsPages\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class CmsPagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Título')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('slug')
                    ->label('URL')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('URL copiada')
                    ->prefix('/pagina/')
                    ->color('gray'),

                IconColumn::make('is_published')
                    ->label('Publicada')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                TextColumn::make('editor.name')
                    ->label('Editado por')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Última modificación')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_published')
                    ->label('Estado')
                    ->placeholder('Todas')
                    ->trueLabel('Publicadas')
                    ->falseLabel('Borradores'),
            ])
            ->recordActions([
                ViewAction::make()
                    ->url(fn ($record) => route('page.show', $record->slug))
                    ->openUrlInNewTab()
                    ->visible(fn ($record) => $record->is_published),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('updated_at', 'desc')
            ->striped();
    }
}
