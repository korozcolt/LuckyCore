<?php

namespace App\Filament\Resources;

use App\Enums\RaffleStatus;
use App\Enums\TicketAssignmentMethod;
use App\Filament\Resources\RaffleResource\Pages;
use App\Filament\Resources\RaffleResource\RelationManagers;
use App\Models\Raffle;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

/**
 * Filament Resource for Raffles.
 *
 * @see PANTALLAS.md §B2 - Sorteos (Raffles)
 */
class RaffleResource extends Resource
{
    protected static ?string $model = Raffle::class;

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-ticket';
    }

    public static function getModelLabel(): string
    {
        return 'Sorteo';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Sorteos';
    }

    public static function getNavigationSort(): ?int
    {
        return 1;
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Sorteos';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Schemas\Components\Tabs::make('Sorteo')
                    ->tabs([
                        // Tab 1: Información básica
                        Schemas\Components\Tabs\Tab::make('Información')
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                Schemas\Components\Section::make('Datos principales')
                                    ->columns(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('title')
                                            ->label('Título')
                                            ->required()
                                            ->maxLength(255)
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn ($state, Schemas\Components\Utilities\Set $set) => $set('slug', Str::slug($state))
                                            ),

                                        Forms\Components\TextInput::make('slug')
                                            ->label('URL amigable')
                                            ->required()
                                            ->maxLength(255)
                                            ->unique(ignoreRecord: true),

                                        Forms\Components\Select::make('status')
                                            ->label('Estado')
                                            ->options(RaffleStatus::class)
                                            ->default(RaffleStatus::Draft)
                                            ->required(),

                                        Forms\Components\Toggle::make('featured')
                                            ->label('Destacado')
                                            ->helperText('Mostrar en sección destacada del home'),
                                    ]),

                                Schemas\Components\Section::make('Descripción')
                                    ->schema([
                                        Forms\Components\Textarea::make('short_description')
                                            ->label('Descripción corta')
                                            ->rows(2)
                                            ->maxLength(500)
                                            ->helperText('Se muestra en las tarjetas de listado'),

                                        Forms\Components\RichEditor::make('description')
                                            ->label('Descripción completa')
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        // Tab 2: Precios y Stock
                        Schemas\Components\Tabs\Tab::make('Precios y Stock')
                            ->icon('heroicon-o-currency-dollar')
                            ->schema([
                                Schemas\Components\Section::make('Configuración de precios')
                                    ->columns(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('ticket_price')
                                            ->label('Precio por ticket')
                                            ->required()
                                            ->numeric()
                                            ->prefix('$')
                                            ->suffix('COP')
                                            ->helperText('En pesos colombianos'),

                                        Forms\Components\TextInput::make('total_tickets')
                                            ->label('Total de tickets')
                                            ->required()
                                            ->numeric()
                                            ->minValue(1),

                                        Forms\Components\TextInput::make('sold_tickets')
                                            ->label('Tickets vendidos')
                                            ->numeric()
                                            ->default(0)
                                            ->disabled()
                                            ->dehydrated(false),

                                        Forms\Components\Placeholder::make('available_tickets')
                                            ->label('Tickets disponibles')
                                            ->content(fn (?Raffle $record) => $record ? number_format($record->available_tickets) : '-'
                                            ),
                                    ]),

                                Schemas\Components\Section::make('Reglas de compra')
                                    ->columns(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('min_purchase_qty')
                                            ->label('Mínimo por compra')
                                            ->required()
                                            ->numeric()
                                            ->default(1)
                                            ->minValue(1),

                                        Forms\Components\TextInput::make('max_purchase_qty')
                                            ->label('Máximo por compra')
                                            ->numeric()
                                            ->nullable()
                                            ->helperText('Dejar vacío para sin límite'),

                                        Forms\Components\TextInput::make('max_per_user')
                                            ->label('Máximo por usuario')
                                            ->numeric()
                                            ->nullable()
                                            ->helperText('Dejar vacío para sin límite'),

                                        Forms\Components\Toggle::make('allow_custom_quantity')
                                            ->label('Permitir cantidad personalizada')
                                            ->helperText('El usuario puede ingresar cantidad libre')
                                            ->live(),

                                        Forms\Components\TextInput::make('quantity_step')
                                            ->label('Incremento de cantidad')
                                            ->numeric()
                                            ->default(1)
                                            ->minValue(1)
                                            ->visible(fn (Schemas\Components\Utilities\Get $get) => $get('allow_custom_quantity')),
                                    ]),
                            ]),

                        // Tab 3: Tickets y Sorteo
                        Schemas\Components\Tabs\Tab::make('Sorteo')
                            ->icon('heroicon-o-sparkles')
                            ->schema([
                                Schemas\Components\Section::make('Método de asignación')
                                    ->schema([
                                        Forms\Components\Select::make('ticket_assignment_method')
                                            ->label('Método de tickets')
                                            ->options(TicketAssignmentMethod::class)
                                            ->default(TicketAssignmentMethod::Random)
                                            ->required()
                                            ->helperText('Random: números aleatorios. Sequential: números consecutivos.'),
                                    ]),

                                Schemas\Components\Section::make('Configuración de números de tickets')
                                    ->description('Define el formato y rango de números para los tickets de este sorteo')
                                    ->schema([
                                        Forms\Components\TextInput::make('ticket_digits')
                                            ->label('Cantidad de dígitos')
                                            ->required()
                                            ->numeric()
                                            ->default(5)
                                            ->minValue(3)
                                            ->maxValue(10)
                                            ->helperText('Número de dígitos que tendrán los tickets (ej: 5 = 00001-99999)')
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function ($state, Schemas\Components\Utilities\Set $set, Schemas\Components\Utilities\Get $get) {
                                                // Auto-calcular max_number basado en dígitos
                                                if ($state && is_numeric($state)) {
                                                    $maxNumber = (int) str_repeat('9', (int) $state);
                                                    $set('ticket_max_number', $maxNumber);
                                                }
                                            }),

                                        Forms\Components\TextInput::make('ticket_min_number')
                                            ->label('Número mínimo')
                                            ->required()
                                            ->numeric()
                                            ->default(1)
                                            ->minValue(1)
                                            ->helperText('Número más bajo del rango de tickets'),

                                        Forms\Components\TextInput::make('ticket_max_number')
                                            ->label('Número máximo')
                                            ->required()
                                            ->numeric()
                                            ->minValue(1)
                                            ->helperText('Número más alto del rango. Debe ser suficiente para el total de tickets.')
                                            ->visible(fn (Schemas\Components\Utilities\Get $get) => $get('ticket_digits'))
                                            ->dehydrated(),
                                    ])
                                    ->columns(3),

                                Schemas\Components\Section::make('Fechas')
                                    ->columns(3)
                                    ->schema([
                                        Forms\Components\DateTimePicker::make('starts_at')
                                            ->label('Fecha de inicio')
                                            ->native(false),

                                        Forms\Components\DateTimePicker::make('ends_at')
                                            ->label('Fecha de cierre')
                                            ->native(false),

                                        Forms\Components\DateTimePicker::make('draw_at')
                                            ->label('Fecha del sorteo')
                                            ->native(false),
                                    ]),

                                Schemas\Components\Section::make('Lotería / Fuente')
                                    ->columns(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('lottery_source')
                                            ->label('Fuente del sorteo')
                                            ->placeholder('Ej: Lotería de Bogotá')
                                            ->maxLength(255),

                                        Forms\Components\TextInput::make('lottery_reference')
                                            ->label('Referencia')
                                            ->placeholder('Ej: Último premio')
                                            ->maxLength(255),
                                    ]),
                            ]),

                        // Tab 4: SEO
                        Schemas\Components\Tabs\Tab::make('SEO')
                            ->icon('heroicon-o-magnifying-glass')
                            ->schema([
                                Schemas\Components\Section::make('Metadatos')
                                    ->schema([
                                        Forms\Components\TextInput::make('meta_title')
                                            ->label('Título SEO')
                                            ->maxLength(70)
                                            ->helperText('Máximo 70 caracteres'),

                                        Forms\Components\Textarea::make('meta_description')
                                            ->label('Descripción SEO')
                                            ->rows(3)
                                            ->maxLength(160)
                                            ->helperText('Máximo 160 caracteres'),

                                        Forms\Components\TextInput::make('sort_order')
                                            ->label('Orden')
                                            ->numeric()
                                            ->default(0)
                                            ->helperText('Menor número = aparece primero'),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Título')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('ticket_price')
                    ->label('Precio/Boleto')
                    ->money('COP')
                    ->sortable(),

                Tables\Columns\TextColumn::make('sold_percentage')
                    ->label('Vendido')
                    ->suffix('%')
                    ->sortable(query: fn (Builder $query, string $direction) => $query->orderByRaw('(sold_tickets / total_tickets) '.$direction)
                    ),

                Tables\Columns\TextColumn::make('total_tickets')
                    ->label('Total')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\IconColumn::make('featured')
                    ->label('Destacado')
                    ->boolean(),

                Tables\Columns\TextColumn::make('draw_at')
                    ->label('Fecha sorteo')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options(RaffleStatus::class),

                Tables\Filters\TernaryFilter::make('featured')
                    ->label('Destacado'),
            ])
            ->actions([
                Actions\ActionGroup::make([
                    Actions\ViewAction::make(),
                    Actions\EditAction::make(),

                    Actions\Action::make('publish')
                        ->label('Publicar')
                        ->icon('heroicon-o-globe-alt')
                        ->color('success')
                        ->requiresConfirmation()
                        ->visible(fn (Raffle $record) => $record->status === RaffleStatus::Draft)
                        ->action(fn (Raffle $record) => $record->update(['status' => RaffleStatus::Active])),

                    Actions\Action::make('close')
                        ->label('Cerrar')
                        ->icon('heroicon-o-lock-closed')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->visible(fn (Raffle $record) => $record->status === RaffleStatus::Active)
                        ->action(fn (Raffle $record) => $record->update(['status' => RaffleStatus::Closed])),

                    Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PackagesRelationManager::class,
            RelationManagers\PrizesRelationManager::class,
            RelationManagers\ImagesRelationManager::class,
            RelationManagers\WinnersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRaffles::route('/'),
            'create' => Pages\CreateRaffle::route('/create'),
            'view' => Pages\ViewRaffle::route('/{record}'),
            'edit' => Pages\EditRaffle::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', RaffleStatus::Active)->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
}
