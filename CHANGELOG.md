# Changelog

Todos los cambios notables en este proyecto serán documentados en este archivo.

El formato está basado en [Keep a Changelog](https://keepachangelog.com/es-ES/1.0.0/),
y este proyecto adhiere a [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

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
