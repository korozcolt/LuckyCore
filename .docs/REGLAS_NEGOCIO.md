# REGLAS_NEGOCIO.md — Reglas críticas

## 1) Carrito (multi-sorteo)
- Carrito existe para invitado (session_id) y usuario (user_id)
- Al login: merge de carrito sesión → usuario
- Validaciones por ítem:
  - min_purchase_qty
  - max_purchase_qty (si aplica)
  - max_per_user (si aplica)
  - stock disponible
- No hay reserva de stock en MVP: el stock se confirma al pagar

## 2) Orden
- Orden se crea desde carrito (order + order_items)
- Se congelan snapshots de precio por item

## 3) Pago
- Pago válido SOLO si webhook verificado lo confirma
- Estados:
  - pending: intento creado
  - paid: webhook aprobado
  - failed: rechazado
  - expired: vencido
- Idempotencia: evento repetido NO duplica tickets

## 4) Tickets
- Se asignan SOLO al aprobar pago
- Método por sorteo:
  - random (default)
  - sequential (configurable)
- Tickets deben ser únicos por sorteo (UNIQUE raffle_id+code)
- **Configuración de números**:
  - Cada sorteo define cantidad de dígitos (ej: 5, 6, 7 dígitos)
  - Cada sorteo define rango: número mínimo y número máximo
  - El rango debe ser suficiente para el total de tickets
  - Ejemplo: sorteo de 1000 tickets puede usar rango 1-9999 (4 dígitos) o 10000-10999 (5 dígitos)

## 5) Stock
- No se permite sobreventa
- La asignación valida stock por sorteo antes de generar tickets
- Si pago aprobado y no hay stock (caso extremo), se registra incidente y se activa reproceso/gestión manual

## 6) Resultados y ganador
- Resultado se registra manualmente (MVP)
- Fórmula de ganador definida y almacenada (auditable)
- Confirmación de ganador queda auditada
- Publicación de resultados crea registro público visible
- **Premios múltiples**:
  - Un sorteo puede tener múltiples premios (1er, 2do, 3er, premios especiales, etc.)
  - Cada premio tiene condiciones de ganancia configurables:
    - Número exacto (ej: 12345)
    - Número al revés (ej: 12345 → 54321)
    - Permutación (cualquier orden de dígitos)
    - Últimos N dígitos (ej: últimos 2 = 45)
    - Primeros N dígitos (ej: primeros 3 = 123)
    - Combinaciones específicas (ej: [1,2,3] en cualquier orden)
  - Un ticket puede ganar múltiples premios si cumple varias condiciones
  - Los premios se calculan automáticamente al registrar el número ganador de la lotería

## 7) Trazabilidad
- Cada orden tiene timeline:
  - order.created, payment.intent_created, webhook.received, payment.approved, tickets.assigned, etc.
- Cualquier error técnico crea evento `*.failed` con meta y error_code

## 8) Imágenes de Sorteos
- Un sorteo puede tener una o varias imágenes de referencia
- Las imágenes son subidas por el administrador/cliente
- Si un sorteo no tiene imágenes:
  - Se muestra solo el título del sorteo
  - Se usa un placeholder visual (icono) en lugar de imagen
- Las imágenes tienen orden (`sort_order`) y una puede marcarse como principal (`is_primary`)
- La imagen principal se muestra en cards y como hero en detalle
- Si hay múltiples imágenes, se muestra galería en detalle del sorteo
