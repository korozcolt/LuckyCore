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

## 5) Stock
- No se permite sobreventa
- La asignación valida stock por sorteo antes de generar tickets
- Si pago aprobado y no hay stock (caso extremo), se registra incidente y se activa reproceso/gestión manual

## 6) Resultados y ganador
- Resultado se registra manualmente (MVP)
- Fórmula de ganador definida y almacenada (auditable)
- Confirmación de ganador queda auditada
- Publicación de resultados crea registro público visible

## 7) Trazabilidad
- Cada orden tiene timeline:
  - order.created, payment.intent_created, webhook.received, payment.approved, tickets.assigned, etc.
- Cualquier error técnico crea evento `*.failed` con meta y error_code
