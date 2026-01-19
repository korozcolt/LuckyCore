<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Winner;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notification sent to raffle winners.
 */
class WinnerNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Winner $winner,
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $raffle = $this->winner->raffle;
        $appName = config('app.name');

        return (new MailMessage)
            ->subject("🎉 ¡Felicidades! Has ganado en {$appName}")
            ->greeting("¡Hola {$this->winner->winner_name}!")
            ->line('¡Tenemos excelentes noticias! Has sido seleccionado como **ganador** en nuestro sorteo.')
            ->line("**Sorteo:** {$raffle->title}")
            ->line("**Premio:** {$this->winner->prize_name}")
            ->line("**Valor del premio:** {$this->winner->formatted_prize_value}")
            ->line("**Tu número ganador:** {$this->winner->ticket_number}")
            ->line('---')
            ->line('Para reclamar tu premio, por favor comunícate con nosotros respondiendo a este correo o a través de nuestros canales de contacto.')
            ->action('Ver mis boletos', url('/mis-boletos'))
            ->line('---')
            ->line('¡Gracias por participar y felicidades nuevamente!')
            ->salutation("Atentamente,\nEl equipo de {$appName}");
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'winner_id' => $this->winner->id,
            'raffle_id' => $this->winner->raffle_id,
            'prize_name' => $this->winner->prize_name,
            'ticket_number' => $this->winner->ticket_number,
        ];
    }
}
