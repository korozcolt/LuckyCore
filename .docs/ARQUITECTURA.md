# ARQUITECTURA.md — Laravel + Livewire + Filament

## 1) Stack
- Backend: Laravel
- Front público: Blade + Livewire (+ Alpine para UX)
- Admin: Filament
- DB: PostgreSQL (recomendado) o MySQL
- Cache/Queue: Redis (opcional, recomendado para emails y reprocesos)
- Logs: canal payments + correlation_id

## 2) Estructura del proyecto
/app
  /Actions
  /Services
  /Payments
  /Models
  /Enums
  /Notifications
  /Jobs
  /Policies
  /Filament
/routes
  web.php (front)
  api.php (webhooks)
/database/migrations

## 3) Principios
- Controladores delgados
- Lógica en Actions/Services
- Transacciones DB para pago+asignación
- Idempotencia estricta en webhooks
- Trazabilidad obligatoria: order_events

## 4) Módulos
- Raffles: gestión de sorteos, paquetes e imágenes
- Cart: carrito multi-sorteo (sesión + usuario)
- Orders: orden multi-item
- Payments: providers + webhooks
- Tickets: generación/asignación
- CMS: páginas editables
- Results: resultados/ganador
- Observability: timeline, incidentes, logs

## 5) Integración pagos
- PaymentProviderContract
- PaymentManager para resolver provider según config
- Webhook endpoints por provider con firma

## 6) Seguridad
- Rate limiting login y checkout
- Webhook verification + idempotencia
- Roles/Policies Filament
- Sanitización CMS
- Headers básicos recomendados
