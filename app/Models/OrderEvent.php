<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * Order event for timeline tracking.
 *
 * @see REGLAS_NEGOCIO.md §7 - Trazabilidad
 */
class OrderEvent extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'payment_transaction_id',
        'event_type',
        'description',
        'metadata',
        'is_error',
        'error_code',
        'error_message',
        'actor_type',
        'actor_id',
        'correlation_id',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'is_error' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (OrderEvent $event) {
            $event->ulid ??= (string) Str::ulid();
            $event->created_at ??= now();
        });
    }

    // Relationships

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(PaymentTransaction::class, 'payment_transaction_id');
    }

    // Event type constants
    public const ORDER_CREATED = 'order.created';

    public const PAYMENT_INTENT_CREATED = 'payment.intent_created';

    public const PAYMENT_REDIRECTED = 'payment.redirected';

    public const WEBHOOK_RECEIVED = 'webhook.received';

    public const PAYMENT_APPROVED = 'payment.approved';

    public const PAYMENT_REJECTED = 'payment.rejected';

    public const PAYMENT_EXPIRED = 'payment.expired';

    public const TICKETS_ASSIGNED = 'tickets.assigned';

    public const TICKETS_FAILED = 'tickets.failed';

    public const ORDER_COMPLETED = 'order.completed';

    public const ORDER_CANCELLED = 'order.cancelled';

    public const USER_ASSOCIATED = 'user.associated';

    public const REFUND_INITIATED = 'refund.initiated';

    public const REFUND_COMPLETED = 'refund.completed';

    public const SUPPORT_NOTE = 'support.note';

    // Actor types
    public const ACTOR_SYSTEM = 'system';

    public const ACTOR_USER = 'user';

    public const ACTOR_ADMIN = 'admin';

    public const ACTOR_WEBHOOK = 'webhook';

    // Factory methods

    public static function log(
        Order $order,
        string $eventType,
        ?string $description = null,
        ?array $metadata = null,
        ?PaymentTransaction $transaction = null,
        bool $isError = false,
        ?string $errorCode = null,
        ?string $errorMessage = null,
        string $actorType = self::ACTOR_SYSTEM,
        ?int $actorId = null,
    ): static {
        return static::create([
            'order_id' => $order->id,
            'payment_transaction_id' => $transaction?->id,
            'event_type' => $eventType,
            'description' => $description,
            'metadata' => $metadata,
            'is_error' => $isError,
            'error_code' => $errorCode,
            'error_message' => $errorMessage,
            'actor_type' => $actorType,
            'actor_id' => $actorId,
            'correlation_id' => $order->correlation_id,
        ]);
    }

    // Computed attributes

    public function getEventLabelAttribute(): string
    {
        return match ($this->event_type) {
            self::ORDER_CREATED => 'Orden creada',
            self::PAYMENT_INTENT_CREATED => 'Intento de pago creado',
            self::PAYMENT_REDIRECTED => 'Redirigido a pasarela',
            self::WEBHOOK_RECEIVED => 'Webhook recibido',
            self::PAYMENT_APPROVED => 'Pago aprobado',
            self::PAYMENT_REJECTED => 'Pago rechazado',
            self::PAYMENT_EXPIRED => 'Pago expirado',
            self::TICKETS_ASSIGNED => 'Tickets asignados',
            self::TICKETS_FAILED => 'Error asignando tickets',
            self::ORDER_COMPLETED => 'Orden completada',
            self::ORDER_CANCELLED => 'Orden cancelada',
            self::REFUND_INITIATED => 'Reembolso iniciado',
            self::REFUND_COMPLETED => 'Reembolso completado',
            self::SUPPORT_NOTE => 'Nota de soporte',
            default => $this->event_type,
        };
    }

    // Query scopes

    public function scopeErrors($query)
    {
        return $query->where('is_error', true);
    }

    public function scopeOfType($query, string $eventType)
    {
        return $query->where('event_type', $eventType);
    }
}
