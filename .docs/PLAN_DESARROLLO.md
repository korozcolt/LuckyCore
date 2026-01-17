# PLAN_DESARROLLO.md — MVP temprano + entrega total <= 2.5 meses

## Sprint 0 (2–3 días)
- Setup repo, envs, stack, roles
- Migraciones base (usuarios, sorteos, carrito, órdenes, pagos, tickets, timeline)

## Sprint 1 (1 semana)
- Front: listado/detalle sorteos + CMS lectura + auth
- Admin: CRUD sorteos básico (sin extras)

## Sprint 2 (1 semana)
- Carrito multi-sorteo (invitado sesión) + merge al login
- Checkout + términos + crear orden multi-item

## Sprint 3 (1–1.5 semanas)
- Provider 1 (Wompi): intent + webhook firma + idempotencia
- Timeline base y vista admin de orden con eventos

## Sprint 4 (1 semana) — MVP OPERATIVO
- Asignación tickets por item + mostrar tickets al usuario
- Admin: órdenes + tickets + acciones soporte básicas
- Staging + UAT
- Entrega MVP

## Sprint 5 (1–1.5 semanas)
- Providers adicionales: MercadoPago + ePayco
- CMS editable (admin) + export CSV básico

## Sprint 6 (1 semana)
- Resultados: registrar, calcular ganador, publicar
- Auditoría mínima + hardening seguridad + QA final
- Producción + capacitación
- Entrega sistema completo
