<div align="center">
  <img src="https://raw.githubusercontent.com/lucide-icons/lucide/main/icons/shield-check.svg" alt="Juara League Logo" width="80" height="80">
  <h1 align="center">Juara League API</h1>
  
  <p align="center">
    <strong>The backend engine for the most flexible tournament management platform.</strong>
  </p>

  <p align="center">
    <i>Built with Laravel 11/13 & Sanctum</i>
  </p>
</div>

---

## 📖 Table of Contents
- [About the API](#-about-the-api)
- [Architecture Documentation](#-architecture-documentation)
- [Technical Stack](#-technical-stack)
- [Getting Started](#-getting-started)
- [Project Documentation](#-project-documentation)

---

## 🚀 About the API

This is the core backend service for **Juara League**, handling complex tournament logic, multi-role authentication, and real-time data serving for the Nuxt-based frontend.

---

## 🏛️ Architecture Documentation

For a deep dive into how this backend is structured, our design patterns, and module breakdown, please refer to the:

### 👉 [Architecture Guide](./ARCHITECTURE.md)

This document covers:
- Layered Architecture (Controllers, Services, Repositories).
- Tournament Engine logic.
- API standards and error handling.
- Database strategy.

---

## 🛠️ Technical Stack

- **Framework**: Laravel 11+
- **Database**: SQLite (Dev) / PostgreSQL (Prod)
- **Auth**: Laravel Sanctum (Stateful)
- **Patterns**: Service-Repository Layers

---

## 💻 Getting Started

### 1. Requirements
- PHP 8.3+
- Composer 2.x

### 2. Installation
```bash
# Install dependencies
composer install

# Setup environment
cp .env.example .env
php artisan key:generate

# Database setup
php artisan migrate --seed

# Start server
php artisan serve
```

---

## 📄 Project Documentation

For business requirements and use cases, see the root `/docs` directory:
- [Business Requirements Document](../docs/BRD.md)
- [Use Cases](../docs/USECASEs.md)

---

## ⚖️ License

The Juara League platform is open-source software licensed under the [MIT license](https://opensource.org/licenses/MIT).

