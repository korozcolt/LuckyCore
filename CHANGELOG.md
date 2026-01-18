# Changelog

Todos los cambios notables en este proyecto serán documentados en este archivo.

El formato está basado en [Keep a Changelog](https://keepachangelog.com/es-ES/1.0.0/),
y este proyecto adhiere a [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2026-01-18

### Added - Sprint 4: Asignación de Tickets & MVP Operativo

#### Sistema de Asignación de Tickets
- `TicketAssignmentService` - Servicio para asignar tickets automáticamente:
  - `assignForOrder()`: Asigna tickets para órdenes pagadas
  - `assignForOrderItem()`: Asignación por item con soporte para métodos secuencial/aleatorio
  - Idempotencia: llamadas múltiples no duplican tickets
  - Soporte para órdenes de invitados (user_id nullable)
  - Logging de eventos en OrderEvent (TICKETS_ASSIGNED, TICKETS_FAILED)
  - Manejo de colisiones de códigos únicos por sorteo

#### Vista de Tickets para Usuario
- Página de orden (`/mis-compras/{order}`) muestra tickets asignados:
  - Grid de números de boleto formateados
  - Indicador de tickets ganadores con highlight
  - Mensaje de espera si tickets están siendo asignados

#### Tests
- `TicketAssignmentServiceTest` con 3 tests:
  - Asignación secuencial para órdenes pagadas
  - Idempotencia ante llamadas múltiples
  - Asignación para órdenes de invitados

### Fixed
- Corregido texto duplicado en hero de página de detalle de sorteo cuando no hay imagen primaria

---

### Added - Sprint 3: Integración de Pagos con Wompi

#### Sistema de Pagos
- `PaymentManager` - Orquestador central de pasarelas de pago:
  - `provider()`: Obtiene instancia del proveedor
  - `activeGateways()`: Lista pasarelas activas
  - Configuración dinámica desde BD
- Interfaz `PaymentProviderInterface` para implementar nuevos proveedores
- DTO `PaymentIntentData` para datos de checkout (amount, signature, widget_url, etc.)

#### Filament (Enums)
- Enums de estado/proveedor implementan `HasLabel`, `HasColor` y `HasIcon` para centralizar badge label/color/icon:
  - `OrderStatus`, `PaymentStatus`, `RaffleStatus`, `WinningConditionType`, `PaymentProvider`, `TicketAssignmentMethod`, `UserRole`
- Resources/RelationManagers/Infolists simplificados para usar el enum como state (sin `match` duplicados en `->color()` / `->formatStateUsing()`).
- Tests unitarios en Pest para asegurar contratos y valores esperados (`tests/Unit/Enums/*`).

#### Integración Wompi
- `WompiPaymentProvider` - Implementación completa:
  - `createPaymentIntent()`: Genera intent con firma de integridad
  - `handleWebhook()`: Procesa eventos de transacción
  - `verifyWebhookSignature()`: Valida checksum SHA256
  - `mapWompiStatus()`: Mapea estados Wompi → PaymentStatus
- Widget de checkout integrado con Alpine.js
- Soporte para múltiples métodos de pago (tarjetas, PSE, Nequi, Bancolombia)
- Configuración por ambiente (sandbox/producción)

#### Página de Pago (`/pago/{order}`)
- Livewire component `Payment\Index`:
  - Selección de pasarela de pago disponible
  - Validación de orden (estado, propiedad)
  - Creación de payment intent
  - Widget de Wompi embebido dinámicamente
- Resumen de orden con items y totales
- Información del cliente
- Badges de seguridad (SSL, pago seguro)

#### Webhooks
- Endpoint `POST /api/webhooks/payments/{provider}`
- Verificación de firma criptográfica
- Actualización automática de:
  - Estado de transacción (pending → approved/rejected)
  - Estado de orden (pending → paid/failed)
  - Timestamps (paid_at, completed_at)
- Idempotencia para webhooks duplicados
- Logging de eventos en OrderEvent

#### Panel Administrativo (Filament)
- `PaymentGatewayResource` - Gestión de pasarelas:
  - Activar/desactivar proveedores
  - Configuración de credenciales (encriptadas)
  - Logo y descripción personalizables
- `OrderResource` ampliado con:
  - Tabs: Información, Items, Transacciones, Timeline, Info técnica
  - Timeline de eventos con badges de colores
  - Detalle de transacciones de pago
  - Metadata técnica (IP, correlation_id, ULID)

#### Tests
- `WompiProviderTest` con 6 tests:
  - Creación de payment intent con firma válida
  - Verificación de signature de webhook
  - Manejo de estados (APPROVED, DECLINED, etc.)
- `PaymentWebhookTest` con 4 tests:
  - Rechazo de provider desconocido
  - Validación de firma
  - Procesamiento de webhook válido
  - Idempotencia ante duplicados
- `PaymentManagerTest` con 3 tests:
  - Obtención de provider por enum
  - Lista de gateways activos
- `PaymentGatewayTest` con 3 tests

#### Configuración
Variables de entorno requeridas:
```env
WOMPI_PUBLIC_KEY=pub_prod_xxx
WOMPI_PRIVATE_KEY=prv_prod_xxx
WOMPI_EVENTS_SECRET=xxx
WOMPI_INTEGRITY_SECRET=xxx
WOMPI_SANDBOX=true
```

---

### Added - Sprint 2: Carrito Multi-Sorteo & Checkout

#### Carrito de Compras
- `CartService` para gestionar operaciones del carrito:
  - `getOrCreateCart()`: Obtiene o crea carrito para sesión/usuario
  - `addItem()`: Agrega items con validaciones de cantidad y stock
  - `updateItem()`: Actualiza cantidad y paquete
  - `removeItem()`: Elimina items del carrito
  - `mergeGuestCart()`: Fusiona carrito de invitado al iniciar sesión
  - `validateCart()`: Valida todo el carrito antes de checkout
- Página de carrito (`/carrito`) con:
  - Lista de items con imagen, título, cantidad y subtotal
  - Controles +/- para modificar cantidad
  - Selector de paquetes por item
  - Botón eliminar con confirmación
  - Resumen de pedido con total
  - Validación de errores por item
- Componente `CartCounter` reactivo en header
- Listener `MergeGuestCartOnLogin` para fusión automática de carritos

#### Checkout y Órdenes
- `CheckoutService` para conversión carrito → orden:
  - `createOrder()`: Crea orden con items y eventos
  - Validación de términos y condiciones
  - Validación de datos del cliente
  - Registro de eventos en timeline
- Página de checkout (`/checkout`) con:
  - Formulario de datos del cliente (nombre, email, teléfono)
  - Resumen de items a comprar
  - Checkbox de términos y condiciones
  - Total a pagar
  - Botón de confirmar con loading state
- Página de detalle de orden (`/mis-compras/{order}`) con:
  - Estado de la orden con badges de colores
  - Lista de items comprados
  - Resumen de totales
  - Código de soporte para WhatsApp
  - Vista de boletos asignados (si pagado)

#### Soporte Guest Checkout
- Migración para hacer `user_id` nullable en `orders`
- Órdenes pueden crearse sin usuario autenticado
- Datos del cliente se capturan en el checkout

#### Tests
- `CartServiceTest` con 14 tests cubriendo:
  - Creación de carritos para guests y usuarios
  - Agregar/actualizar/eliminar items
  - Validaciones de cantidad y stock
  - Merge de carritos
- `CheckoutServiceTest` con 7 tests cubriendo:
  - Creación de órdenes para usuarios y guests
  - Cálculo correcto de totales
  - Validaciones de términos y email
  - Registro de eventos

#### Factories
- `CartFactory` para crear carritos de prueba
- `CartItemFactory` para items de carrito
- `RafflePackageFactory` para paquetes de sorteo

### Added - Sprint 1.5: Reglas de Negocio Críticas

#### Configuración de Números de Tickets
- Campos configurables por sorteo: `ticket_digits`, `ticket_min_number`, `ticket_max_number`
- Validaciones en modelo Raffle:
  - ticket_digits entre 3-10 dígitos
  - ticket_max_number >= ticket_min_number
  - Rango suficiente para total_tickets
- Formulario en Filament con auto-cálculo de número máximo
- Formato dinámico de tickets según configuración del sorteo
- Tests unitarios y de integración completos

#### Sistema de Premios Múltiples
- Modelo `RafflePrize` para múltiples premios por sorteo
- Enum `WinningConditionType` con 6 tipos de condiciones:
  - `exact_match`: Número exacto
  - `reverse`: Número al revés
  - `permutation`: Cualquier permutación de dígitos
  - `last_digits`: Últimos N dígitos
  - `first_digits`: Primeros N dígitos
  - `combination`: Combinación personalizada
- `PrizesRelationManager` en Filament para gestionar premios
- `PrizeCalculationService` para calcular ganadores automáticamente:
  - `calculateWinners()`: Identifica ganadores por cada premio
  - `applyWinners()`: Guarda resultados en BD
  - `previewWinners()`: Vista previa sin modificar BD
- Vista pública de premios en detalle de sorteo
- Tests completos para el servicio de cálculo

#### Páginas de Autenticación
- Rediseño de login y registro con estilo LuckyCore
- Nuevo favicon con icono de ticket verde
- Layout de autenticación personalizado (`x-layouts.auth`)

### Added - Sprint 1: Admin Panel & Public Views

#### Panel Administrativo (Filament 5)
- `RaffleResource` - CRUD completo para sorteos con:
  - Formulario organizado en tabs (General, Stock, Reglas, SEO)
  - Vista de lista con filtros por estado y búsqueda
  - Estados con colores distintivos (badges)
  - Estadísticas de progreso de venta
- `RafflePackageRelationManager` - Gestión de paquetes de boletos
  - Crear/editar paquetes con nombre, cantidad y precio
  - Indicador de paquete recomendado
  - Cálculo automático de descuento
  - Ordenamiento drag-and-drop
- `RaffleImageRelationManager` - Gestión de galería
  - Upload múltiple de imágenes
  - Imagen primaria destacada
  - Ordenamiento de imágenes

#### Páginas Públicas (Livewire 4)
- Layout público con:
  - Header sticky con navegación
  - Logo y menú responsive (móvil)
  - Carrito con contador de items
  - Footer con enlaces legales y WhatsApp
  - Soporte para dark mode
- Home page (`/`) con:
  - Hero section con sorteo destacado
  - Grid de sorteos activos
  - Sección "¿Cómo Funciona?" con 3 pasos
  - Progress bars animados
- Listado de sorteos (`/sorteos`) con:
  - Filtros por estado (tabs)
  - Contador de resultados
  - Cards con imagen, precio y progreso
  - Estados visuales diferenciados
  - Paginación
- Detalle de sorteo (`/sorteos/{slug}`) con:
  - Hero de ancho completo con imagen de fondo
  - Countdown timer en tiempo real
  - Barra de progreso de ventas
  - Grid de paquetes seleccionables
  - Selector de cantidad personalizada
  - Sidebar sticky con resumen de compra
  - FAQ accordion expandible
  - Galería de imágenes
- Páginas CMS (`/pagina/{slug}`) con:
  - Contenido HTML renderizado
  - FAQ con acordeones expandibles
  - Botón de soporte WhatsApp

#### Diseño y UI
- Integración de fuente "Be Vietnam Pro" (Google Fonts)
- Material Symbols Outlined para iconografía
- Esquema de colores personalizado:
  - Primary: `#13ec13` (verde brillante)
  - Background light: `#f6f8f6`
  - Background dark: `#102210`
  - Text: `#111811`, `#618961`
- Tailwind CSS v4 con @theme para variables CSS
- Diseño responsive mobile-first
- Dark mode completo

#### Datos de Prueba
- `SampleDataSeeder` con:
  - 5 sorteos de ejemplo (3 activos, 1 próximo, 1 finalizado)
  - Paquetes de boletos con descuentos progresivos
  - Páginas CMS: Cómo funciona, Términos, FAQ

### Changed
- Actualizado `public.blade.php` para usar diseño de mockups
- Actualizado estilos en `app.css` con variables de tema personalizadas

---

### Added - Sprint 0: Setup Base

#### Stack y Dependencias
- Laravel 12 con Livewire 4 + Flux como base
- Filament 5 para panel administrativo (`/admin`)
- Spatie Laravel Permission para sistema de roles
- Pest para testing

#### Enums del Dominio
- `RaffleStatus`: draft, upcoming, active, closed, completed, cancelled
- `OrderStatus`: pending, paid, failed, expired, refunded, partial_refund
- `PaymentStatus`: pending, processing, approved, rejected, expired, refunded, voided
- `TicketAssignmentMethod`: random (default), sequential
- `PaymentProvider`: wompi, mercadopago, epayco
- `UserRole`: customer, support, admin, super_admin

#### Migraciones
- `raffles` - Sorteos con stock, precios, reglas, método de tickets
- `raffle_packages` - Paquetes por sorteo (ej: 50/70/100 tickets)
- `raffle_images` - Galería de imágenes por sorteo
- `carts` - Carrito por sesión o usuario
- `cart_items` - Items del carrito multi-sorteo
- `orders` - Órdenes con support_code y correlation_id
- `order_items` - Items con snapshot de precios
- `payment_transactions` - Transacciones con idempotency_key
- `tickets` - Tickets únicos por sorteo (UNIQUE raffle_id+code)
- `order_events` - Timeline para trazabilidad
- `cms_pages` - Páginas CMS editables
- `raffle_results` - Resultados con auditoría

#### Modelos
- `Raffle` con relaciones a packages, images, tickets, result
- `RafflePackage` con cálculo de descuento
- `RaffleImage` con URL de storage
- `Cart` con merge de sesión a usuario
- `CartItem` con subtotal calculado
- `Order` con generación de order_number y support_code
- `OrderItem` con tracking de tickets asignados
- `PaymentTransaction` con idempotency
- `Ticket` con código único por sorteo
- `OrderEvent` con factory method para logging
- `CmsPage` con slugs predefinidos
- `RaffleResult` con workflow de confirmación/publicación

#### Sistema de Roles
- 4 roles: customer, support, admin, super_admin
- 37 permisos distribuidos por módulo
- Control de acceso a Filament por rol
- Métodos helper en User: `isAdmin()`, `isSupport()`, `isSuperAdmin()`

#### Configuración
- Canal de logs `payments` con 90 días de retención
- Estructura de directorios: Actions, Services, Payments, Enums, Policies, Jobs, Notifications

#### Seeders
- `RolesAndPermissionsSeeder` - Roles y permisos del sistema
- Usuarios de prueba por rol (solo en local/testing)

### Security
- Webhook signature verification preparado
- Rate limiting configurado en arquitectura
- Idempotency keys para transacciones de pago

---

## Leyenda

- **Added** - Nuevas funcionalidades
- **Changed** - Cambios en funcionalidades existentes
- **Deprecated** - Funcionalidades que serán eliminadas
- **Removed** - Funcionalidades eliminadas
- **Fixed** - Corrección de bugs
- **Security** - Correcciones de vulnerabilidades
