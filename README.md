# 🎟️ LuckyCore

<div align="center">

<img src="public/images/logo.webp" alt="LuckyCore" width="260" />

**Plataforma Integral de Sorteos Digitales**  
*Gestión profesional de rifas, tickets, pagos y administración*

[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel)](https://laravel.com)
[![Livewire](https://img.shields.io/badge/Livewire-4.x-4E56A6?style=for-the-badge&logo=livewire)](https://livewire.laravel.com)
[![Filament](https://img.shields.io/badge/Filament-5.x-F59E0B?style=for-the-badge&logo=php)](https://filamentphp.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php)](https://php.net)

🚀 Demo • 📖 Documentación • 🐛 Reportar Bug • 💡 Solicitar Feature

Concepto gráfico por **Kristian Orozco**.

</div>

---

## 📖 Tabla de Contenidos

- [📖 Acerca del Proyecto](#-acerca-del-proyecto)
- [🎯 Objetivos](#-objetivos)
- [✨ Características Principales](#-características-principales)
- [🧩 Módulos del Sistema](#-módulos-del-sistema)
- [🏗️ Arquitectura](#-arquitectura)
- [🛠️ Stack Tecnológico](#-stack-tecnológico)
- [🔐 Roles y Permisos](#-roles-y-permisos)
- [⚙️ Instalación](#-instalación)
- [🚀 Inicio Rápido](#-inicio-rápido)
- [🎮 Comandos Útiles](#-comandos-útiles)
- [🛡️ Seguridad y Performance](#-seguridad-y-performance)
- [🧪 Testing](#-testing)
- [📚 Documentación Técnica](#-documentación-técnica)
- [📄 Licencia](#-licencia)

---

## 📖 Acerca del Proyecto

**LuckyCore** es una plataforma web moderna diseñada para **digitalizar, automatizar y profesionalizar la gestión de sorteos y rifas**.

Centraliza en un solo sistema:

- Creación y administración de sorteos
- Venta de tickets mediante carrito multi-sorteo
- Procesamiento de pagos en línea
- Generación y control de órdenes
- Publicación de resultados y ganadores
- Panel administrativo robusto y seguro

Está pensada para **emprendimientos digitales**, **marcas**, **comunidades**, y **operadores recurrentes de sorteos** que requieren trazabilidad, control financiero y escalabilidad.

---

## 🎯 Objetivos

- ✅ Digitalizar completamente la operación de sorteos
- ✅ Simplificar la compra de tickets para usuarios finales
- ✅ Centralizar pagos y órdenes en un solo flujo
- ✅ Garantizar transparencia en resultados
- ✅ Proveer un panel administrativo potente
- ✅ Escalar múltiples sorteos de forma simultánea

---

## ✨ Características Principales

### 🎟️ Gestión de Sorteos
- Creación de sorteos con imágenes y configuración flexible
- Control de estados: activos, programados y finalizados
- Gestión de cupos y disponibilidad de tickets

### 🛒 Carrito Multi-Sorteo
- Compra de tickets de múltiples sorteos en una sola orden
- Persistencia por sesión y usuario autenticado
- Actualización dinámica de cantidades

### 💳 Procesamiento de Pagos
- Integración con pasarelas:
  - Wompi
  - MercadoPago
  - ePayco
- Estados de pago auditables
- Registro detallado de transacciones

### 🧾 Órdenes y Tickets
- Órdenes multi-item
- Generación automática de tickets por sorteo
- Relación clara entre usuario, orden y tickets

### 🏆 Resultados
- Cálculo de ganadores
- Publicación controlada de resultados
- Historial completo de sorteos cerrados

### 🛠️ Panel Administrativo
- Gestión completa desde Filament
- Control de sorteos, pagos y usuarios
- Acceso basado en roles y permisos

---

## 🧩 Módulos del Sistema

| Módulo | Descripción |
|------|------------|
| **Raffles** | Gestión de sorteos, precios e imágenes |
| **Cart** | Carrito multi-sorteo (sesión + usuario) |
| **Orders** | Órdenes multi-item con estados |
| **Payments** | Wompi, MercadoPago, ePayco |
| **Tickets** | Generación y asignación automática |
| **CMS** | Páginas editables (FAQ, Términos, Cómo funciona) |
| **Results** | Cálculo y publicación de ganadores |

---

## 🏗️ Arquitectura

```

LuckyCore/
├── app/
│   ├── Actions/          # Lógica de negocio
│   ├── Enums/            # Estados del dominio
│   ├── Models/           # Modelos Eloquent
│   ├── Payments/         # Providers de pago
│   ├── Services/         # Servicios de dominio
│   ├── Jobs/             # Procesos en cola
│   ├── Policies/         # Autorización
│   └── Notifications/    # Notificaciones
│
├── database/
│   ├── migrations/
│   └── seeders/
│
├── resources/
│   ├── views/            # Livewire + Blade
│   └── js/               # Assets frontend
│
└── routes/
├── web.php
└── admin.php

````

Arquitectura orientada a **servicios**, mantenible y preparada para escalar.

---

## 🛠️ Stack Tecnológico

### Backend
- Laravel 12
- PHP 8.2+
- MySQL 8 / PostgreSQL 14+

### Frontend Público
- Livewire 4
- Flux UI
- Alpine.js
- Tailwind CSS

### Panel Administrativo
- Filament 5

### Infraestructura
- Redis (cache y colas recomendado)
- Jobs asíncronos
- Logs especializados por dominio

---

## 🔐 Roles y Permisos

| Rol | Acceso Admin | Descripción |
|----|--------------|-------------|
| **customer** | ❌ | Usuario comprador |
| **support** | ⚠️ | Soporte operativo |
| **admin** | ✅ | Gestión completa |
| **super_admin** | ✅ | Control total |

Sistema basado en **policies** y **permisos granulares**.

---

## ⚙️ Instalación

```bash
# Clonar repositorio
git clone <repository-url>
cd LuckyCore

# Instalar dependencias
composer install
npm install

# Configuración inicial
cp .env.example .env
php artisan key:generate

# Base de datos
php artisan migrate --seed

# Compilar assets
npm run build

# Iniciar entorno de desarrollo
composer dev
````

---

## 👤 Usuarios por Defecto

| Email                                               | Contraseña | Rol         |
| --------------------------------------------------- | ---------- | ----------- |
| [admin@luckycore.com](mailto:admin@luckycore.com)   | password   | Super Admin |
| [admin@example.com](mailto:admin@example.com)       | password   | Admin       |
| [support@example.com](mailto:support@example.com)   | password   | Soporte     |
| [customer@example.com](mailto:customer@example.com) | password   | Cliente     |

---

## 🚀 Inicio Rápido

```bash
# Panel administrativo
http://localhost:8000/admin

# Frontend público
http://localhost:8000
```

---

## 🎮 Comandos Útiles

```bash
# Desarrollo
composer dev

# Testing
php artisan test
php artisan test --parallel

# Cache
php artisan optimize
php artisan optimize:clear

# Jobs
php artisan queue:work

# Linting
composer lint
```

---

## 🛡️ Seguridad y Performance

* Validación estricta de pagos
* Logs separados para transacciones
* Protección CSRF
* Policies en todos los recursos
* Jobs en background
* Cache de consultas frecuentes

---

## 🧪 Testing

* Tests de dominio
* Tests de pagos
* Tests de órdenes y tickets
* Soporte para ejecución paralela

```bash
php artisan test
```

---

## 📚 Documentación Técnica

Disponible en `/.docs`:

* `ALCANCE.md` – Scope del proyecto
* `ARQUITECTURA.md` – Arquitectura técnica
* `REGLAS_NEGOCIO.md` – Reglas de negocio
* `PANTALLAS.md` – UI/UX
* `PLAN_DESARROLLO.md` – Roadmap por sprints

---

## 📄 Licencia

🔒 **Proyecto Privado**
Todos los derechos reservados.

---

<div align="center">

**🎟️ LuckyCore**
*Infraestructura digital para sorteos modernos*

</div>
