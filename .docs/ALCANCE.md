# ALCANCE.md — Plataforma Web de Sorteos

## 1) Objetivo
Construir una plataforma web de sorteos que permita publicar sorteos, vender boletos en un carrito multi-sorteo, procesar pagos (Wompi/MercadoPago/ePayco), asignar tickets automáticamente y operar resultados desde un panel administrativo.

## 2) Actores
- Visitante: navega, consulta, agrega al carrito (sesión), se registra/inicia sesión.
- Cliente (logueado): compra, paga, consulta órdenes y tickets, solicita soporte.
- Administrador: gestiona sorteos/paquetes/stock, órdenes/pagos, tickets, CMS, resultados.
- Soporte (opcional): consulta órdenes/tickets/timeline, acciones limitadas.

## 3) Alcance incluido (Sistema completo)
### Plataforma pública
- Home + Listado sorteos + Detalle sorteo + Cómo funciona + Términos + FAQ
- Indicadores: estado sorteo, % vendido, precio por ticket, paquetes
- Botón soporte (WhatsApp con mensaje prellenado)

### Carrito y compra
- Carrito multi-sorteo: agregar, modificar, eliminar ítems
- Carrito para invitado por sesión, con **merge al login**
- Checkout: resumen, aceptación de términos, selección pasarela
- Orden multi-item (un pago para varios sorteos)
- Confirmación: aprobado/pendiente/rechazado/expirado

### Pagos
- Integración con pasarelas: Wompi, MercadoPago, ePayco (configurables)
- Confirmación por webhook (firma + idempotencia)
- Estados de pago: pending/approved/rejected/expired/refunded (refunded si aplica)

### Tickets
- Asignación de tickets por ítem al aprobar pago
- Tickets no secuenciales por defecto; configurable por sorteo (random/sequential)
- Visualización de tickets en “Mis compras” y detalle de orden

### Panel administrativo (Filament)
- Dashboard operativo básico
- CRUD sorteos, paquetes, imágenes, reglas, método tickets
- Órdenes/pagos con filtros + detalle + acciones soporte
- Tickets: búsqueda + export básico
- CMS: Cómo funciona, Términos, FAQ
- Resultados: registrar resultado, calcular ganador, publicar
- Auditoría mínima + timeline por orden

## 4) Alcance NO incluido (para evitar sobrecostos)
- App móvil nativa
- Reservas de stock por tiempo en carrito (bloqueo de cupos)
- Analítica avanzada tipo BI / panel “alerts” complejo
- Integración automática con fuentes externas de loterías
- Facturación electrónica / temas fiscales
- Referidos/afiliados, cupones avanzados (cupones básicos opcionales si cabe)

## 5) Decisiones cerradas
- Front público: Livewire
- Backoffice: Filament
- Carrito sin login: sí (por sesión + merge al login)
- Paquetes: sí
- Cantidad libre: opcional por sorteo (flag allow_custom_quantity)

## 6) Entregables
- Plataforma funcional en producción (sistema completo)
- Panel admin completo
- Documentación operativa (uso admin + soporte)
- Capacitación admin
- Soporte correctivo post-lanzamiento (según acuerdo)

## 7) Hitos
- Hito 1: MVP Operativo (compra multi-sorteo + 1 pasarela + tickets + admin básico + timeline)
- Hito 2: Sistema completo (3 pasarelas + resultados + CMS + exports + hardening + producción)

## 8) Criterios de aceptación (resumen)
- Invitado agrega 2 sorteos al carrito → login → carrito se conserva
- Checkout crea una orden multi-item
- Pago aprobado por webhook asigna tickets por sorteo
- Usuario ve tickets en Mis compras
- Admin ve timeline y transacciones, y puede operar resultados
