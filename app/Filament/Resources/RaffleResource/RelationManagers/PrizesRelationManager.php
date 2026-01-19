<?php

namespace App\Filament\Resources\RaffleResource\RelationManagers;

use App\Enums\WinningConditionType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Relation Manager for Raffle Prizes.
 *
 * @see REGLAS_NEGOCIO.md §6 - Premios múltiples
 * @see ANALISIS_REGLAS_NEGOCIO.md §3 - Premios Múltiples con Combinaciones
 */
class PrizesRelationManager extends RelationManager
{
    protected static string $relationship = 'prizes';

    protected static ?string $title = 'Premios';

    protected static ?string $modelLabel = 'Premio';

    protected static ?string $pluralModelLabel = 'Premios';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Información del premio')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre del premio')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Ej: Primer Premio, Premio Especial'),

                        Textarea::make('description')
                            ->label('Descripción')
                            ->rows(2)
                            ->maxLength(500),

                        TextInput::make('prize_value')
                            ->label('Valor del premio')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->suffix('COP')
                            ->helperText('Valor monetario del premio'),

                        TextInput::make('prize_position')
                            ->label('Posición')
                            ->required()
                            ->numeric()
                            ->default(1)
                            ->minValue(1)
                            ->helperText('Orden del premio (1 = primer premio, 2 = segundo, etc.)'),
                    ])
                    ->columns(2),

                Section::make('Condiciones de ganancia')
                    ->description('Define cómo se determina el ganador de este premio')
                    ->schema([
                        Select::make('winning_conditions.type')
                            ->label('Tipo de condición')
                            ->options(WinningConditionType::class)
                            ->required()
                            ->live()
                            ->helperText(fn ($state) => $state ?
                                WinningConditionType::tryFrom($state)?->getDescription() : 'Selecciona un tipo'),

                        TextInput::make('winning_conditions.digit_count')
                            ->label('Cantidad de dígitos')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(10)
                            ->default(2)
                            ->visible(fn (Get $get) => in_array($get('winning_conditions.type'), [
                                WinningConditionType::LastDigits->value,
                                WinningConditionType::FirstDigits->value,
                            ]))
                            ->helperText('Número de dígitos a comparar'),
                    ])
                    ->columns(2),

                Section::make('Configuración')
                    ->schema([
                        TextInput::make('sort_order')
                            ->label('Orden de visualización')
                            ->numeric()
                            ->default(0)
                            ->helperText('Para ordenar los premios en la vista pública'),

                        Toggle::make('is_active')
                            ->label('Activo')
                            ->default(true)
                            ->helperText('Los premios inactivos no se muestran ni se calculan'),
                    ])
                    ->columns(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                Tables\Columns\TextColumn::make('prize_position')
                    ->label('#')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('prize_value')
                    ->label('Valor')
                    ->money('COP')
                    ->sortable(),

                Tables\Columns\TextColumn::make('winning_conditions.type')
                    ->label('Condición')
                    ->badge()
                    ->placeholder('-')
                    ->state(fn ($state) => WinningConditionType::tryFrom($state)),

                Tables\Columns\TextColumn::make('winning_conditions.digit_count')
                    ->label('Dígitos')
                    ->placeholder('-')
                    ->alignCenter(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),

                Tables\Columns\TextColumn::make('winningTickets_count')
                    ->counts('winningTickets')
                    ->label('Ganadores')
                    ->badge()
                    ->color('success'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Activo'),

                Tables\Filters\SelectFilter::make('winning_conditions->type')
                    ->label('Tipo de condición')
                    ->options(WinningConditionType::class),
            ])
            ->headerActions([
                CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        // Convert prize_value to cents
                        $data['prize_value'] = (int) ($data['prize_value'] * 100);

                        return $data;
                    }),
            ])
            ->actions([
                EditAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['prize_value'] = (int) ($data['prize_value'] * 100);

                        return $data;
                    })
                    ->mutateRecordDataUsing(function (array $data): array {
                        $data['prize_value'] = $data['prize_value'] / 100;

                        return $data;
                    }),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
