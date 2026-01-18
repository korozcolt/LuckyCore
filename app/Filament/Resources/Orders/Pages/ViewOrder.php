<?php

declare(strict_types=1);

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use App\Models\OrderEvent;
use App\Services\TicketAssignmentService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Database\Eloquent\Model;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('assignTickets')
                ->label('Asignar tickets')
                ->icon('heroicon-o-ticket')
                ->color('primary')
                ->requiresConfirmation()
                ->visible(function (Order $record): bool {
                    return $record->isPaid() && (! $record->allTicketsAssigned());
                })
                ->action(function (TicketAssignmentService $ticketAssignmentService, Order $record): void {
                    $ticketAssignmentService->assignForOrder($record);

                    $this->refreshFormData(['status']);

                    Notification::make()
                        ->title('Tickets asignados')
                        ->success()
                        ->send();
                }),

            Action::make('addSupportNote')
                ->label('Nota soporte')
                ->icon('heroicon-o-chat-bubble-left-ellipsis')
                ->color('gray')
                ->form([
                    \Filament\Forms\Components\Textarea::make('note')
                        ->label('Nota')
                        ->required()
                        ->rows(4),
                ])
                ->action(function (array $data, Order $record): void {
                    OrderEvent::log(
                        order: $record,
                        eventType: OrderEvent::SUPPORT_NOTE,
                        description: $data['note'],
                        actorType: OrderEvent::ACTOR_ADMIN,
                        actorId: auth()->id(),
                    );

                    Notification::make()
                        ->title('Nota guardada')
                        ->success()
                        ->send();
                }),
        ];
    }

    protected function resolveRecord(int|string $key): Model
    {
        return parent::resolveRecord($key)->load([
            'user',
            'items.raffle',
            'items.package',
            'transactions',
            'tickets.raffle',
            'events' => fn ($q) => $q->orderBy('created_at', 'desc'),
        ]);
    }
}
