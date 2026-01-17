<?php

namespace App\Filament\Resources\RaffleResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Relation Manager for Raffle Images.
 *
 * @see PANTALLAS.md §A3 - Galería (slider)
 */
class ImagesRelationManager extends RelationManager
{
    protected static string $relationship = 'images';

    protected static ?string $title = 'Imágenes';

    protected static ?string $modelLabel = 'Imagen';

    protected static ?string $pluralModelLabel = 'Imágenes';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\FileUpload::make('path')
                    ->label('Imagen')
                    ->image()
                    ->required()
                    ->disk('public')
                    ->directory('raffles')
                    ->imageResizeMode('cover')
                    ->imageCropAspectRatio('16:9')
                    ->imageResizeTargetWidth('1920')
                    ->imageResizeTargetHeight('1080')
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('alt_text')
                    ->label('Texto alternativo')
                    ->maxLength(255)
                    ->helperText('Descripción de la imagen para accesibilidad'),

                Forms\Components\Toggle::make('is_primary')
                    ->label('Imagen principal')
                    ->helperText('Se muestra como portada del sorteo'),

                Forms\Components\TextInput::make('sort_order')
                    ->label('Orden')
                    ->numeric()
                    ->default(0),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('alt_text')
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                Tables\Columns\ImageColumn::make('path')
                    ->label('Imagen')
                    ->disk('public')
                    ->width(120)
                    ->height(68),

                Tables\Columns\TextColumn::make('alt_text')
                    ->label('Texto alternativo')
                    ->limit(30)
                    ->placeholder('Sin descripción'),

                Tables\Columns\IconColumn::make('is_primary')
                    ->label('Principal')
                    ->boolean(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Orden')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_primary')
                    ->label('Principal'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
