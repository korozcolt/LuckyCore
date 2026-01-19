<?php

declare(strict_types=1);

namespace App\Filament\Resources\RaffleResource\Pages;

use App\Enums\RaffleStatus;
use App\Filament\Resources\RaffleResource;
use App\Models\Raffle;
use App\Models\RaffleResult;
use App\Services\WinnerCalculationService;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;

class ViewRaffle extends ViewRecord
{
    protected static string $resource = RaffleResource::class;

    protected static ?string $title = 'Ver Sorteo';

    public function getBreadcrumb(): string
    {
        return 'Ver';
    }

    protected function getHeaderActions(): array
    {
        /** @var Raffle $raffle */
        $raffle = $this->record;

        return [
            Actions\Action::make('registerResult')
                ->label('Registrar Resultado')
                ->icon('heroicon-o-trophy')
                ->color('success')
                ->visible(fn () => in_array($raffle->status, [RaffleStatus::Closed, RaffleStatus::Completed]))
                ->form([
                    Section::make('Datos de la Lotería')
                        ->schema([
                            TextInput::make('lottery_name')
                                ->label('Nombre de la lotería')
                                ->default($raffle->lottery_source)
                                ->placeholder('Ej: Lotería de Bogotá')
                                ->maxLength(255),

                            TextInput::make('lottery_number')
                                ->label('Número ganador')
                                ->required()
                                ->placeholder('Ej: 12345')
                                ->helperText('Ingresa el número ganador de la lotería')
                                ->maxLength(20),

                            DatePicker::make('lottery_date')
                                ->label('Fecha del sorteo')
                                ->default(now())
                                ->required(),
                        ])
                        ->columns(3),

                    Section::make('Opciones')
                        ->schema([
                            Toggle::make('notify_winners')
                                ->label('Notificar ganadores por email')
                                ->default(true)
                                ->helperText('Enviar email de felicitación a los ganadores'),

                            Toggle::make('publish_results')
                                ->label('Publicar resultados')
                                ->default(true)
                                ->helperText('Hacer públicos los ganadores inmediatamente'),
                        ])
                        ->columns(2),
                ])
                ->action(function (array $data) use ($raffle): void {
                    // Create or update raffle result
                    $result = RaffleResult::updateOrCreate(
                        ['raffle_id' => $raffle->id],
                        [
                            'lottery_name' => $data['lottery_name'],
                            'lottery_number' => $data['lottery_number'],
                            'lottery_date' => $data['lottery_date'],
                            'registered_by' => auth()->id(),
                        ]
                    );

                    // Calculate winners
                    $service = app(WinnerCalculationService::class);
                    $winners = $service->calculateWinners(
                        $result,
                        auth()->id(),
                        notify: $data['notify_winners'],
                        publish: $data['publish_results'],
                    );

                    // Update raffle status to Completed
                    if ($raffle->status !== RaffleStatus::Completed) {
                        $raffle->update(['status' => RaffleStatus::Completed]);
                    }

                    Notification::make()
                        ->title('Resultado registrado')
                        ->body("Se encontraron {$winners->count()} ganador(es)")
                        ->success()
                        ->send();
                })
                ->modalWidth('2xl')
                ->modalHeading('Registrar Resultado del Sorteo'),

            Actions\Action::make('viewResult')
                ->label('Ver Resultado')
                ->icon('heroicon-o-eye')
                ->color('info')
                ->visible(fn () => $raffle->result !== null)
                ->modalHeading('Resultado del Sorteo')
                ->modalContent(fn () => view('filament.pages.raffle-result', [
                    'raffle' => $raffle,
                    'result' => $raffle->result,
                    'winners' => $raffle->winners()->with('testimonial')->get(),
                ])),

            Actions\EditAction::make()
                ->label('Editar'),
        ];
    }
}
