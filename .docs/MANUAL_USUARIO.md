# Manual de Usuario — LuckyCore

Este manual describe, **paso a paso**, el uso del sistema LuckyCore para:

- Cliente final (plataforma pública)
- Soporte (operación y atención)
- Administrador (panel `/admin`)

Incluye flujos completos, estados, troubleshooting de pagos/webhooks, y operación de resultados/ganadores/testimonios.

> Capturas: se guardan en `/.docs/manual/assets/` con los nombres indicados en cada sección.

---

## Índice

- 0) Convenciones y capturas
- 1) Cliente final (plataforma pública)
- 2) Soporte (operación y atención)
- 3) Administrador (panel Filament)
- 4) Pagos y webhooks (operación + troubleshooting)
- 5) Resultados, ganadores y testimonios
- 6) Glosario de estados y referencias

---

## 0) Convenciones y capturas

### 0.1) URLs principales

- Público: `https://luckycore.test/`
- Admin: `https://luckycore.test/admin`

### 0.2) Cómo tomar capturas en Chrome

1. Abre la página.
2. Activa “Disable cache” en DevTools (Network).
3. Captura:
   - macOS: `Shift + Command + 4` (selección) o `Shift + Command + 5` (opciones)
   - Windows: `Win + Shift + S`
4. Guarda la imagen en `/.docs/manual/assets/` con el nombre indicado.

### 0.3) Nombres de archivos para capturas

Formato:

`/.docs/manual/assets/<rol>/<modulo>/<paso>-<slug>.png`

Ejemplo:

`/.docs/manual/assets/cliente/checkout/03-terminos-y-pagar.png`

---

## 1) Cliente final (plataforma pública)

Este capítulo cubre el recorrido del cliente desde “ver sorteos” hasta “ver mis compras y tickets”.

### 1.1) Navegación básica

#### 1.1.1) Entrar al sitio

1. Visita `https://luckycore.test/`.
2. Verifica el header:
   - Inicio / Sorteos / Cómo funciona
   - Mis compras (si estás logueado)
   - ícono de carrito

**Captura:** `/.docs/manual/assets/cliente/home/01-header.png`

### 1.2) Ver sorteos

#### 1.2.1) Listado de sorteos

1. En el menú, entra a “Sorteos”.
2. Confirma que cada card muestra:
   - imagen (o placeholder)
   - título
   - estado (Activo / Próximo / Finalizado)
   - % vendido + barra
   - precio por ticket

**Captura:** `/.docs/manual/assets/cliente/sorteos/01-listado.png`

#### 1.2.2) Detalle del sorteo

1. Haz clic en un sorteo.
2. Revisa galería/imagen principal, precio, progreso, paquetes disponibles y CTA de compra.

**Captura:** `/.docs/manual/assets/cliente/sorteos/02-detalle.png`

### 1.3) Carrito (multi-sorteo)

#### 1.3.1) Agregar al carrito

1. En el detalle del sorteo, selecciona un paquete.
2. Haz clic en “Agregar al carrito”.
3. Verifica el contador del carrito en el header.

**Captura:** `/.docs/manual/assets/cliente/carrito/01-agregar.png`

#### 1.3.2) Revisar y ajustar carrito

1. Entra a `https://luckycore.test/carrito`.
2. Ajusta cantidades (si aplica) y revisa el total.
3. Si hay validaciones (stock, mínimos), deben mostrarse.

**Captura:** `/.docs/manual/assets/cliente/carrito/02-carrito.png`

### 1.4) Checkout y pago

#### 1.4.1) Checkout

1. En carrito, presiona “Continuar al checkout”.
2. Completa nombre, email, teléfono.
3. Acepta términos y condiciones.

**Captura:** `/.docs/manual/assets/cliente/checkout/01-checkout.png`

#### 1.4.2) Seleccionar pasarela y pagar

1. Selecciona la pasarela disponible.
2. Presiona “Pagar”.
3. Completa el flujo en la pasarela (según proveedor).

**Captura:** `/.docs/manual/assets/cliente/pago/01-widget.png`

### 1.5) Confirmación, mis compras y tickets

#### 1.5.1) Confirmación de orden

1. Tras pagar, valida la pantalla de confirmación de orden.
2. Si el pago queda aprobado, deben aparecer los tickets (o el estado “asignando”).

**Captura:** `/.docs/manual/assets/cliente/orden/01-confirmacion.png`

#### 1.5.2) Mis compras

1. Entra a `https://luckycore.test/mis-compras`.
2. Abre una orden.
3. Verifica:
   - estado
   - items
   - tickets por sorteo
   - código de soporte (para WhatsApp)

**Capturas:**

- `/.docs/manual/assets/cliente/compras/01-listado.png`
- `/.docs/manual/assets/cliente/compras/02-detalle.png`

---

## 2) Soporte (operación y atención)

Objetivo: atender clientes y operar incidentes comunes.

### 2.1) Información mínima que debe pedir soporte

- Código de soporte de la orden (lo ve el cliente en el detalle de la compra).
- Email del cliente.
- Proveedor de pago usado (Wompi/MercadoPago/ePayco).
- Estado que ve el cliente (pendiente/aprobado/rechazado).

### 2.2) Flujos de atención típicos

#### 2.2.1) “Pagué pero no veo mis tickets”

Checklist:
1. Verificar estado de orden (¿está pagada?).
2. Revisar si hay evento de “tickets asignados” o “tickets fallidos”.
3. Si está pagada y faltan tickets: reprocesar asignación desde admin (si aplica).

**Captura:** `/.docs/manual/assets/soporte/orden/01-estado.png`

#### 2.2.2) “El pago quedó pendiente”

Checklist:
1. Explicar que el estado se confirma por webhook (puede tardar).
2. Revisar en admin la transacción más reciente y el timeline.
3. Si el proveedor ya marcó aprobado pero el webhook no llegó: revisar sección 4 (Pagos y webhooks).

### 2.3) Troubleshooting rápido

#### 2.3.1) Qué revisar en Chrome DevTools (si el cliente manda captura)

- Network:
  - confirmar navegación a `/pagar/{order}`
  - revisar si la pasarela cargó el widget correctamente

#### 2.3.2) Qué revisar en admin

- Orden: estado + timeline + transacciones.

---

## 3) Administrador (panel Filament)

Este capítulo cubre la operación completa desde el panel administrativo.

### 3.1) Acceso y navegación

1. Entrar a `https://luckycore.test/admin`.
2. Iniciar sesión con un usuario con rol permitido.

**Capturas:**

- `/.docs/manual/assets/admin/acceso/01-login.png`
- `/.docs/manual/assets/admin/acceso/02-dashboard.png`

### 3.2) Gestión de sorteos

#### 3.2.1) Crear sorteo (checklist mínimo)

- Título, descripción
- Estado
- Precio por ticket, stock total
- Reglas min/max y método de asignación de tickets (random/sequential)
- Configuración de números de ticket (dígitos, min/max)
- Imágenes (principal + galería)
- Paquetes (si aplica)

**Capturas:**

- `/.docs/manual/assets/admin/sorteos/01-crear.png`
- `/.docs/manual/assets/admin/sorteos/02-imagenes.png`
- `/.docs/manual/assets/admin/sorteos/03-paquetes.png`

#### 3.2.2) Publicar / cerrar / finalizar

Documentar con captura:
1. Qué significa cada estado.
2. Cuándo debe usarse.
3. Impacto en la compra y en resultados.

### 3.3) Órdenes y pagos

#### 3.3.1) Ver órdenes

1. Entrar a “Orders”.
2. Filtrar por estado (pendiente/pagada/fallida/expirada).
3. Abrir una orden y revisar tabs:
   - Información
   - Items
   - Transacciones
   - Timeline
   - Tickets

**Capturas:**

- `/.docs/manual/assets/admin/ordenes/01-listado.png`
- `/.docs/manual/assets/admin/ordenes/02-detalle.png`

#### 3.3.2) Asignar tickets manualmente (si aplica)

1. En una orden pagada sin tickets completos, usar acción “Asignar tickets”.
2. Verificar que no duplica tickets (idempotente).

**Captura:** `/.docs/manual/assets/admin/ordenes/03-asignar-tickets.png`

### 3.4) CMS

1. Editar páginas (Cómo funciona / Términos / FAQ).
2. Verificar en público la URL `/pagina/{slug}`.

### 3.5) Usuarios y roles

1. Crear/editar usuarios.
2. Asignar roles.
3. Validar acceso a `/admin` según rol.

### 3.6) Pasarelas de pago

1. Configurar credenciales.
2. Activar/desactivar proveedores.
3. Ordenar por prioridad.

---

## 4) Pagos y webhooks (operación + troubleshooting)

### 4.1) Concepto clave: la fuente de verdad es el webhook

En LuckyCore, el **estado oficial** del pago/orden lo define el webhook verificado:

- Endpoint: `POST /api/webhooks/payments/{provider}`
- Se valida firma y se procesa el payload.

El callback/redirect del usuario solo sirve para UX (mostrar mensajes y redirigir).

### 4.2) Flujo estándar (paso a paso)

1. El usuario inicia pago desde `/pagar/{order}`.
2. Se crea un intento (transacción pendiente).
3. El proveedor procesa el pago.
4. El proveedor envía un webhook a LuckyCore.
5. LuckyCore:
   - verifica firma,
   - actualiza transacción,
   - actualiza orden,
   - asigna tickets si aprobado.

**Capturas recomendadas:**

- `/.docs/manual/assets/ops/webhooks/01-endpoint.png` (DevTools / log)
- `/.docs/manual/assets/ops/webhooks/02-orden-timeline.png` (timeline en admin)

### 4.3) Firmas por proveedor (alto nivel)

#### 4.3.1) Wompi

- Firma basada en `properties + timestamp + secret` → `sha256`.

#### 4.3.2) ePayco

- Firma basada en campos `x_*` → `sha256` con separador `^`.

#### 4.3.3) MercadoPago

- Firma `x-signature`/`x-request-id` cuando aplica.
- Hay eventos donde no viene firma; se requiere validación adicional vía API (si se habilita).

### 4.4) Incidentes comunes

#### 4.4.1) Pago aprobado en pasarela, orden sigue pendiente

Checklist:
1. Confirmar si el webhook llegó (logs / timeline / transacciones).
2. Si llegó, validar firma (401 indica firma inválida).
3. Si no llegó, revisar:
   - URL del webhook en configuración del proveedor
   - red/SSL
   - proveedor en modo sandbox vs producción

#### 4.4.2) Webhook duplicado

El sistema debe ser idempotente: el duplicado no debe duplicar tickets.

### 4.5) Pruebas recomendadas (UAT)

- Simular una compra completa por cada proveedor activo.
- Validar transiciones:
  - pending → approved
  - pending → rejected/expired

---

## 5) Resultados, ganadores y testimonios

### 5.1) Cerrar un sorteo

1. Cambiar el estado del sorteo según el flujo operativo definido.
2. Verificar que ya no permite compras.

### 5.2) Registrar resultado (número ganador)

1. En admin, abrir el sorteo.
2. Usar acción “Registrar Resultado”.
3. Completar:
   - nombre de lotería
   - número ganador
   - fecha
4. Elegir:
   - notificar ganadores
   - publicar resultados

**Capturas:**

- `/.docs/manual/assets/admin/resultados/01-registrar.png`
- `/.docs/manual/assets/admin/resultados/02-confirmacion.png`

### 5.3) Validar ganadores en admin

1. En el sorteo, revisar relación “Ganadores”.
2. Verificar:
   - premio/posición
   - ticket ganador
   - estado publicado/notificado/entregado

### 5.4) Publicación en el sitio

1. Verificar `https://luckycore.test/ganadores`.
2. Verificar sección de ganadores en el detalle del sorteo (si aplica).

**Capturas:**

- `/.docs/manual/assets/cliente/ganadores/01-pagina.png`
- `/.docs/manual/assets/cliente/ganadores/02-detalle-sorteo.png`

### 5.5) Testimonios de ganadores (moderación)

1. Revisar testimonios en admin.
2. Aprobar/rechazar.
3. Destacar testimonios aprobados.

---

## 6) Glosario de estados y referencias

### 6.1) Estados de orden

Las órdenes se confirman “oficialmente” por webhook verificado.

### 6.2) Estados de pago

Dependen del proveedor, pero se normalizan internamente.

### 6.3) Referencias útiles

- Plan del proyecto: `.docs/PLAN_DESARROLLO.md`
- Alcance: `.docs/ALCANCE.md`
- Pantallas/UX: `.docs/PANTALLAS.md`
- Reglas de negocio: `.docs/REGLAS_NEGOCIO.md`
