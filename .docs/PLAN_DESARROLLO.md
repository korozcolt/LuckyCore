# PLAN_DESARROLLO.md — MVP temprano + entrega total <= 2.5 meses

## Sprint 0 (2–3 días)
- Setup repo, envs, stack, roles
- Migraciones base (usuarios, sorteos, carrito, órdenes, pagos, tickets, timeline)
**Estado:** ✅ Completado

## Sprint 1 (1 semana)
- Front: listado/detalle sorteos + CMS lectura + auth
- Admin: CRUD sorteos básico (sin extras)
**Estado:** ✅ Completado

## Sprint 2 (1 semana)
- Carrito multi-sorteo (invitado sesión) + merge al login
- Checkout + términos + crear orden multi-item
**Estado:** ✅ Completado

## Sprint 3 (1–1.5 semanas)
- Provider 1 (Wompi): intent + webhook firma + idempotencia
- Timeline base y vista admin de orden con eventos
**Estado:** ✅ Completado

## Sprint 4 (1 semana) — MVP OPERATIVO
- Asignación tickets por item + mostrar tickets al usuario
- Admin: órdenes + tickets + acciones soporte básicas
- Staging + UAT
- Entrega MVP
**Estado:** ✅ Completado

## Sprint 5 (1–1.5 semanas) ✅ COMPLETADO
- ✅ Providers adicionales: MercadoPago + ePayco
- ✅ CMS editable (admin)
- ✅ Control de acceso admin: Solo SuperAdmin y Admin acceden al panel
- ✅ Gestión de usuarios y roles desde el admin panel
- ✅ Conversión automática de invitado a usuario registrado al comprar
- ✅ Widgets de analíticas en dashboard:
  - StatsOverview: 6 métricas generales
  - TodaySales: ventas del día con comparación vs ayer
  - OrdersChart: tendencia de órdenes 30 días
  - PageViewsStats: tráfico del sitio (visitas, únicos, páginas top)

## Sprint 6 (1 semana)
- Resultados: registrar, calcular ganador, publicar
- Auditoría mínima + hardening seguridad + QA final
- Manual de usuario (Cliente/Soporte/Admin) en `.docs/` con paso a paso y capturas
- Producción + capacitación
- Entrega sistema completo
**Estado:** 🟡 Parcial (resultados/ganadores implementados; hardening/QA/producción/manual en progreso)

### Pendientes sugeridos para cerrar Sprint 6
- Admin “Tickets” dedicado: búsqueda + filtros + export CSV (ver `.docs/ALCANCE.md` y `.docs/PANTALLAS.md` B4).
- Notificación a ganadores “guest” (email) o estrategia operativa definida.
- Hardening recomendado en `.docs/ARQUITECTURA.md`:
  - rate limit para checkout/pago (si aplica a requisitos),
  - revisión de cabeceras/seguridad (CSP/headers según despliegue),
  - revisión operativa de webhooks (monitoring/alertas).
