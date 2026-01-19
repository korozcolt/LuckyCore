<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\WinnerTestimonialResource\Pages;
use App\Models\WinnerTestimonial;
use Filament\Actions;
use Filament\Actions\BulkAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Filament Resource for Winner Testimonials moderation.
 */
class WinnerTestimonialResource extends Resource
{
    protected static ?string $model = WinnerTestimonial::class;

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-chat-bubble-left-right';
    }

    public static function getModelLabel(): string
    {
        return 'Testimonio';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Testimonios';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Sorteos';
    }

    public static function getNavigationSort(): ?int
    {
        return 3;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Testimonio')
                    ->schema([
                        Textarea::make('comment')
                            ->label('Comentario')
                            ->rows(4)
                            ->maxLength(1000),

                        FileUpload::make('photo_path')
                            ->label('Foto')
                            ->image()
                            ->disk('public')
                            ->directory('testimonials')
                            ->visibility('public')
                            ->maxSize(5120),

                        Select::make('rating')
                            ->label('Calificación')
                            ->options([
                                1 => '⭐ 1 estrella',
                                2 => '⭐⭐ 2 estrellas',
                                3 => '⭐⭐⭐ 3 estrellas',
                                4 => '⭐⭐⭐⭐ 4 estrellas',
                                5 => '⭐⭐⭐⭐⭐ 5 estrellas',
                            ]),
                    ])
                    ->columns(1),

                Section::make('Configuración de privacidad')
                    ->schema([
                        Toggle::make('show_full_name')
                            ->label('Mostrar nombre completo')
                            ->helperText('Si está desactivado, se mostrará solo el primer nombre e inicial'),

                        Toggle::make('is_featured')
                            ->label('Destacado')
                            ->helperText('Mostrar en la sección destacada del sitio'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('winner.raffle.title')
                    ->label('Sorteo')
                    ->searchable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('winner.winner_name')
                    ->label('Ganador')
                    ->searchable(),

                Tables\Columns\TextColumn::make('winner.prize_name')
                    ->label('Premio')
                    ->limit(20),

                Tables\Columns\ImageColumn::make('photo_path')
                    ->label('Foto')
                    ->disk('public')
                    ->circular(),

                Tables\Columns\TextColumn::make('comment')
                    ->label('Comentario')
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->comment),

                Tables\Columns\TextColumn::make('rating')
                    ->label('Rating')
                    ->formatStateUsing(fn ($state) => $state ? str_repeat('⭐', $state) : '-'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Pendiente',
                        'approved' => 'Aprobado',
                        'rejected' => 'Rechazado',
                        default => $state,
                    }),

                Tables\Columns\IconColumn::make('is_featured')
                    ->label('Destacado')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'pending' => 'Pendiente',
                        'approved' => 'Aprobado',
                        'rejected' => 'Rechazado',
                    ]),

                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('Destacado'),
            ])
            ->actions([
                Actions\ViewAction::make()
                    ->infolist([
                        Section::make('Ganador')
                            ->schema([
                                TextEntry::make('winner.winner_name')
                                    ->label('Nombre'),
                                TextEntry::make('winner.raffle.title')
                                    ->label('Sorteo'),
                                TextEntry::make('winner.prize_name')
                                    ->label('Premio'),
                            ])
                            ->columns(3),

                        Section::make('Testimonio')
                            ->schema([
                                TextEntry::make('comment')
                                    ->label('Comentario')
                                    ->columnSpanFull(),
                                ImageEntry::make('photo_path')
                                    ->label('Foto')
                                    ->disk('public'),
                                TextEntry::make('rating')
                                    ->label('Calificación')
                                    ->formatStateUsing(fn ($state) => $state ? str_repeat('⭐', $state) : 'Sin calificación'),
                            ]),

                        Section::make('Moderación')
                            ->schema([
                                TextEntry::make('status')
                                    ->label('Estado')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'pending' => 'warning',
                                        'approved' => 'success',
                                        'rejected' => 'danger',
                                        default => 'gray',
                                    }),
                                TextEntry::make('moderator.name')
                                    ->label('Moderado por')
                                    ->placeholder('-'),
                                TextEntry::make('moderated_at')
                                    ->label('Fecha moderación')
                                    ->dateTime()
                                    ->placeholder('-'),
                                TextEntry::make('rejection_reason')
                                    ->label('Razón de rechazo')
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->status === 'rejected'),
                            ])
                            ->columns(3),
                    ]),

                Actions\Action::make('approve')
                    ->label('Aprobar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (WinnerTestimonial $record) => $record->status !== 'approved')
                    ->requiresConfirmation()
                    ->action(fn (WinnerTestimonial $record) => $record->approve(auth()->id())),

                Actions\Action::make('reject')
                    ->label('Rechazar')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (WinnerTestimonial $record) => $record->status !== 'rejected')
                    ->form([
                        Textarea::make('reason')
                            ->label('Razón del rechazo')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(fn (WinnerTestimonial $record, array $data) => $record->reject(
                        auth()->id(),
                        $data['reason']
                    )),

                Actions\Action::make('toggleFeatured')
                    ->label(fn (WinnerTestimonial $record) => $record->is_featured ? 'Quitar destacado' : 'Destacar')
                    ->icon(fn (WinnerTestimonial $record) => $record->is_featured ? 'heroicon-o-star' : 'heroicon-s-star')
                    ->color('warning')
                    ->visible(fn (WinnerTestimonial $record) => $record->status === 'approved')
                    ->action(fn (WinnerTestimonial $record) => $record->toggleFeatured()),

                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    BulkAction::make('approveAll')
                        ->label('Aprobar seleccionados')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->approve(auth()->id())),

                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWinnerTestimonials::route('/'),
            'edit' => Pages\EditWinnerTestimonial::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('status', 'pending')->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}
