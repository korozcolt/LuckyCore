<?php

declare(strict_types=1);

namespace App\Filament\Resources\CmsPages\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CmsPageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Página')
                    ->columnSpanFull()
                    ->tabs([
                        Tabs\Tab::make('Contenido')
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                Section::make('Información básica')
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('title')
                                            ->label('Título')
                                            ->required()
                                            ->maxLength(255)
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn ($state, $set, $context) => $context === 'create'
                                                ? $set('slug', Str::slug($state))
                                                : null
                                            ),

                                        TextInput::make('slug')
                                            ->label('URL amigable')
                                            ->required()
                                            ->maxLength(255)
                                            ->unique(ignoreRecord: true)
                                            ->helperText('Se usará en la URL: /pagina/{slug}'),
                                    ]),

                                Section::make('Contenido de la página')
                                    ->schema([
                                        RichEditor::make('content')
                                            ->label('Contenido principal')
                                            ->helperText('Contenido HTML de la página. Para FAQ, usa la pestaña "Secciones FAQ".')
                                            ->toolbarButtons([
                                                'bold',
                                                'italic',
                                                'underline',
                                                'strike',
                                                'h2',
                                                'h3',
                                                'bulletList',
                                                'orderedList',
                                                'link',
                                                'blockquote',
                                                'redo',
                                                'undo',
                                            ])
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        Tabs\Tab::make('Secciones FAQ')
                            ->icon('heroicon-o-question-mark-circle')
                            ->schema([
                                Section::make('Preguntas frecuentes')
                                    ->description('Agrega preguntas y respuestas para páginas tipo FAQ. Estas se mostrarán como acordeón.')
                                    ->schema([
                                        Repeater::make('sections')
                                            ->label('Preguntas y Respuestas')
                                            ->schema([
                                                TextInput::make('question')
                                                    ->label('Pregunta')
                                                    ->required()
                                                    ->maxLength(500),

                                                Textarea::make('answer')
                                                    ->label('Respuesta')
                                                    ->required()
                                                    ->rows(3),
                                            ])
                                            ->columns(1)
                                            ->addActionLabel('Agregar pregunta')
                                            ->reorderable()
                                            ->collapsible()
                                            ->collapsed()
                                            ->itemLabel(fn (array $state): ?string => $state['question'] ?? null)
                                            ->defaultItems(0),
                                    ]),
                            ]),

                        Tabs\Tab::make('SEO')
                            ->icon('heroicon-o-magnifying-glass')
                            ->schema([
                                Section::make('Metadatos SEO')
                                    ->description('Optimiza la página para motores de búsqueda.')
                                    ->schema([
                                        TextInput::make('meta_title')
                                            ->label('Meta título')
                                            ->maxLength(70)
                                            ->helperText('Máximo 70 caracteres. Se usa en la pestaña del navegador y resultados de búsqueda.'),

                                        Textarea::make('meta_description')
                                            ->label('Meta descripción')
                                            ->maxLength(160)
                                            ->rows(3)
                                            ->helperText('Máximo 160 caracteres. Descripción que aparece en Google.'),
                                    ]),
                            ]),

                        Tabs\Tab::make('Publicación')
                            ->icon('heroicon-o-eye')
                            ->schema([
                                Section::make('Estado de publicación')
                                    ->columns(2)
                                    ->schema([
                                        Toggle::make('is_published')
                                            ->label('Publicada')
                                            ->helperText('Solo las páginas publicadas son visibles al público.')
                                            ->live()
                                            ->afterStateUpdated(function ($state, Get $get, Set $set): void {
                                                if ($state && ! $get('published_at')) {
                                                    $set('published_at', now());
                                                }
                                            }),

                                        DateTimePicker::make('published_at')
                                            ->label('Fecha de publicación')
                                            ->helperText('Fecha en que se publicó la página.')
                                            ->seconds(false),
                                    ]),
                            ]),
                    ]),
            ]);
    }
}
