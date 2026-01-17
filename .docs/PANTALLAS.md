# PANTALLAS.md — UX detallado (Front público + Admin)

## A) Front público

### A1) Home
- Menú: Inicio / Sorteos / Cómo funciona / Mis compras / Iniciar sesión / Carrito (icon)
- Sección “Sorteos activos” (cards)
- CTA “Ver sorteos”

### A2) Listado de sorteos
- Card:
  - Imagen, título
  - Estado: Activo/Próximo/Finalizado
  - % vendido + barra
  - Precio por ticket
  - Botón principal:
    - Activo: “Ver y comprar”
    - Próximo: “Ver”
    - Finalizado: “Ver resultados”
- Click card → Detalle sorteo

### A3) Detalle de sorteo
- Galería (slider)
- Datos:
  - % vendido + barra
  - precio por ticket
  - fecha/hora sorteo (si aplica)
  - condiciones resumidas
- Bloque compra:
  - Botones de paquetes (ej: 50/70/100/120 recomendado)
  - Total dinámico
  - Botón “Agregar al carrito”
    - Acción: crea/obtiene carrito sesión, agrega ítem
    - Respuesta: toast “Agregado” + link “Ver carrito”
  - Si allow_custom_quantity=true:
    - Input cantidad + validación min/step/max
- Acordeones: Términos / Características / FAQ
- Botón “Soporte WhatsApp” (mensaje: sorteo + link a orden si existe)

### A4) Carrito (multi-sorteo)
- Lista de ítems:
  - título sorteo
  - cantidad
  - subtotal
  - acciones:
    - + / - cantidad (respeta reglas)
    - cambiar a paquete (si aplica)
    - remover ítem
- Resumen:
  - total general
  - validaciones visibles si hay conflictos
- Botón “Continuar al checkout”

### A5) Checkout
- Resumen de ítems + total
- Checkbox obligatorio: “Acepto términos”
- Selector pasarela (si hay varias habilitadas)
- Botón “Pagar”
  - Acción: convertir carrito a orden + crear intento pago + redireccionar/widget

### A6) Confirmación de pago
- Estado mostrado:
  - Aprobado: mostrar tickets por sorteo + botón “Ir a Mis compras”
  - Pendiente: mensaje + botón “Actualizar estado”
  - Rechazado/Expirado: mensaje + botón “Reintentar pago”
- Mostrar “Código de soporte” (order_id/correlation) para atención rápida

### A7) Autenticación
- Registro / Login / Recuperar
- Evento especial: al login, merge carrito sesión → usuario

### A8) Mis compras
- Listado de órdenes:
  - fecha, total, estado, pasarela
  - botón “Ver detalle”
- Detalle orden:
  - timeline básico para el cliente (opcional)
  - ítems por sorteo
  - tickets por sorteo
  - botón “Soporte WhatsApp” (order_id incluido)

---

## B) Admin (Filament)

### B1) Dashboard
- KPIs: ventas hoy, órdenes por estado, tickets vendidos por sorteo
- Bloque “pendientes” y “incidentes”

### B2) Sorteos (Raffles)
- Crear/editar:
  - título, descripción, estado
  - precio ticket, stock total
  - reglas min/max
  - método tickets random/sequential
  - allow_custom_quantity + min/step
  - fecha cierre/sorteo
  - lotería/fuente
  - imágenes
  - paquetes + recomendado
- Acciones: publicar/cerrar/finalizar

### B3) Órdenes
- Listado + filtros
- Detalle:
  - ítems por sorteo
  - transacciones/payloads
  - timeline completo (order_events)
  - acciones: reenviar email, reprocesar tickets, reconsultar pago (si aplica)

### B4) Tickets
- Buscar por ticket code
- filtros por sorteo/orden/usuario
- export CSV

### B5) CMS
- Editar cómo funciona / términos / FAQ

### B6) Resultados
- Registrar resultado
- Calcular ganador
- Confirmar y publicar
- Auditoría de cambios críticos
