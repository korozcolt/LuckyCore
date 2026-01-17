# ANÁLISIS_REGLAS_NEGOCIO.md — Análisis de Reglas de Negocio Críticas

Este documento analiza el cumplimiento de tres reglas de negocio críticas en el código actual.

**Última actualización:** 2026-01-17

---

## 1. Imágenes de Sorteos

### Regla de Negocio
- Un sorteo debe tener una o varias imágenes de referencia subidas por el cliente
- Si no tiene imágenes, solo se muestra el título del sorteo

### Estado Actual: ✅ IMPLEMENTADO

#### Lo que existe:
- ✅ Modelo `RaffleImage` con relación `hasMany` desde `Raffle`
- ✅ Campo `is_primary` para marcar imagen principal
- ✅ Campo `sort_order` para ordenar imágenes
- ✅ `ImagesRelationManager` en Filament para gestionar imágenes
- ✅ Vista muestra imagen principal en cards (`raffle->primaryImage`)
- ✅ Vista muestra galería si hay más de una imagen
- ✅ Placeholder con icono cuando no hay imágenes

#### Archivos clave:
- `app/Models/RaffleImage.php`
- `app/Filament/Resources/RaffleResource/RelationManagers/ImagesRelationManager.php`

---

## 2. Números de Tickets Configurables

### Regla de Negocio
- Los sorteos se hacen con tickets numéricos
- La cantidad de dígitos es asignada desde la creación del sorteo
- El administrador decide el número mínimo y el número máximo

### Estado Actual: ✅ IMPLEMENTADO

#### Lo que existe:
- ✅ **Campo `ticket_digits`**: Cantidad de dígitos del número de ticket (3-10 dígitos)
- ✅ **Campo `ticket_min_number`**: Número mínimo del rango
- ✅ **Campo `ticket_max_number`**: Número máximo del rango
- ✅ **Validación**: `ticket_max_number` debe ser >= `ticket_min_number`
- ✅ **Validación**: Rango suficiente para `total_tickets`
- ✅ **Formato dinámico**: `getFormattedCodeAttribute()` usa `ticket_digits` del sorteo
- ✅ **Formulario Filament**: Campos configurables con auto-cálculo de máximo
- ✅ **Tests completos**: Unitarios y de integración

#### Archivos clave:
- `database/migrations/2026_01_17_091631_add_ticket_number_config_to_raffles_table.php`
- `app/Models/Raffle.php` (validaciones y casts)
- `app/Models/Ticket.php` (`getFormattedCodeAttribute()`)
- `app/Filament/Resources/RaffleResource.php` (formulario)
- `tests/Feature/RaffleTicketNumberConfigTest.php`

---

## 3. Premios Múltiples con Combinaciones

### Regla de Negocio
- Los sorteos normalmente tienen varios premios, no solo uno
- Los números x, y, z ganan tanto
- Los números a, b, c ganan tanto
- Bajo la combinación x ganan tanto
- Puede ser los mismos números en desorden
- Puede ser los mismos números pero al revés
- Un sin número de posibilidades

### Estado Actual: ✅ IMPLEMENTADO

#### Lo que existe:
- ✅ **Modelo `RafflePrize`** con campos:
  - `raffle_id`
  - `name` (nombre del premio)
  - `description` (descripción opcional)
  - `prize_value` (valor en centavos)
  - `prize_position` (orden: 1, 2, 3...)
  - `winning_conditions` (JSON con tipo y configuración)
  - `is_active` (boolean)
  - `sort_order` (orden de visualización)

- ✅ **Enum `WinningConditionType`** con 6 tipos de condiciones:
  - `exact_match`: Número exacto
  - `reverse`: Número al revés
  - `permutation`: Cualquier permutación de dígitos
  - `last_digits`: Últimos N dígitos
  - `first_digits`: Primeros N dígitos
  - `combination`: Combinación personalizada

- ✅ **`PrizeCalculationService`** con métodos:
  - `calculateWinners()`: Identifica ganadores por premio
  - `applyWinners()`: Guarda resultados en BD
  - `previewWinners()`: Vista previa sin modificar BD

- ✅ **`PrizesRelationManager`** en Filament para gestionar premios
- ✅ **Vista pública** muestra premios disponibles en detalle de sorteo
- ✅ **Tests completos** para el servicio de cálculo

#### Archivos clave:
- `database/migrations/2026_01_17_100000_create_raffle_prizes_table.php`
- `database/migrations/2026_01_17_100001_add_prize_id_to_tickets_table.php`
- `app/Models/RafflePrize.php`
- `app/Enums/WinningConditionType.php`
- `app/Services/PrizeCalculationService.php`
- `app/Filament/Resources/RaffleResource/RelationManagers/PrizesRelationManager.php`
- `resources/views/livewire/pages/raffles/show.blade.php`
- `tests/Feature/PrizeCalculationServiceTest.php`
- `database/factories/RafflePrizeFactory.php`

---

## Resumen de Estado

| Regla de Negocio | Estado | Sprint |
|-----------------|--------|--------|
| Imágenes de Sorteos | ✅ Implementado | Sprint 1 |
| Números de Tickets Configurables | ✅ Implementado | Sprint 1.5 |
| Premios Múltiples | ✅ Implementado | Sprint 1.5 |

---

## Próximos Pasos

Las tres reglas de negocio críticas están implementadas. Los siguientes sprints según `PLAN_DESARROLLO.md`:

- **Sprint 2**: Carrito multi-sorteo + Checkout
- **Sprint 3**: Integración de pagos (Wompi)
- **Sprint 4**: Asignación de tickets + Vista admin de órdenes
