# LuckyCore

Plataforma web de sorteos con carrito multi-sorteo, procesamiento de pagos y panel administrativo.

## Stack Tecnológico

- **Backend:** Laravel 12
- **Frontend Público:** Livewire 4 + Flux + Alpine.js
- **Panel Admin:** Filament 5
- **Base de Datos:** MySQL / PostgreSQL
- **Cache/Queue:** Redis (recomendado)

## Requisitos

- PHP 8.2+
- Composer
- Node.js 18+
- MySQL 8.0+ o PostgreSQL 14+

## Instalación

```bash
# Clonar repositorio
git clone <repository-url>
cd LuckyCore

# Instalar dependencias
composer install
npm install

# Configurar entorno
cp .env.example .env
php artisan key:generate

# Configurar base de datos en .env
# DB_CONNECTION=mysql
# DB_DATABASE=luckycore
# ...

# Ejecutar migraciones y seeders
php artisan migrate --seed

# Compilar assets
npm run build

# Iniciar servidor de desarrollo
composer dev
```

## Usuarios por Defecto

| Email | Contraseña | Rol |
|-------|------------|-----|
| admin@luckycore.com | password | Super Admin |
| admin@example.com | password | Admin |
| support@example.com | password | Soporte |
| customer@example.com | password | Cliente |

## Estructura del Proyecto

```
app/
├── Actions/          # Lógica de negocio
├── Enums/            # Enums del dominio
├── Models/           # Modelos Eloquent
├── Payments/         # Providers de pago
├── Policies/         # Políticas de autorización
├── Services/         # Servicios
├── Jobs/             # Jobs de cola
└── Notifications/    # Notificaciones

database/
├── migrations/       # Migraciones
└── seeders/          # Seeders
```

## Módulos

| Módulo | Descripción |
|--------|-------------|
| Raffles | Gestión de sorteos, paquetes e imágenes |
| Cart | Carrito multi-sorteo (sesión + usuario) |
| Orders | Órdenes multi-item con timeline |
| Payments | Proveedores: Wompi, MercadoPago, ePayco |
| Tickets | Generación y asignación de tickets |
| CMS | Páginas editables (Cómo funciona, Términos, FAQ) |
| Results | Resultados y cálculo de ganador |

## Roles y Permisos

| Rol | Acceso Admin | Descripción |
|-----|--------------|-------------|
| customer | No | Cliente registrado |
| support | Sí (limitado) | Consulta órdenes/tickets, acciones básicas |
| admin | Sí | Gestión operativa completa |
| super_admin | Sí | Todo + gestión de usuarios/roles |

## Comandos Útiles

```bash
# Desarrollo
composer dev              # Servidor + queue + logs + vite

# Testing
php artisan test          # Ejecutar tests
php artisan test --parallel  # Tests en paralelo

# Linting
composer lint             # Ejecutar Pint

# Cache
php artisan optimize      # Cache de config/routes/views
php artisan optimize:clear  # Limpiar cache
```

## Documentación

La documentación del proyecto se encuentra en `/.docs`:

- `ALCANCE.md` - Scope del proyecto
- `PANTALLAS.md` - Especificaciones de UI/UX
- `ARQUITECTURA.md` - Arquitectura técnica
- `REGLAS_NEGOCIO.md` - Reglas de negocio
- `PLAN_DESARROLLO.md` - Plan de desarrollo por sprints

## Logs

- **General:** `storage/logs/laravel.log`
- **Pagos:** `storage/logs/payments.log` (90 días retención)

## Licencia

Proyecto privado. Todos los derechos reservados.
