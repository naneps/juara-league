# 🏛️ Juara League Backend Architecture

This document outlines the architectural design, patterns, and technical standards for the Juara League API. It serves as a guide for developers to maintain consistency and scalability as the platform grows from MVP to a full SaaS solution.

---

## 🚀 1. Overview

The **Juara League API** is a headless backend service built with Laravel 11/13. It is designed to be:
- **Stateless & Scalable**: Ready for containerized deployment.
- **SaaS-Ready**: Multi-tenant foundation for future subscription models.
- **Multi-Format**: Versatile logic to handle various tournament structures (Single Elim, Double Elim, Round Robin, Swiss).

---

## 🛠️ 2. Technology Stack

- **Framework**: [Laravel 11+](https://laravel.com/) (Lean Structure)
- **Language**: [PHP 8.3+](https://www.php.net/)
- **Authentication**: [Laravel Sanctum](https://laravel.com/docs/11.x/sanctum) (Stateful SPA Auth)
- **Database**: 
    - **Development**: SQLite
    - **Production**: PostgreSQL
- **Testing**: [PHPUnit](https://phpunit.de/) / [Pest](https://pestphp.com/)
- **API Standard**: RESTful JSON API

---

## 🧩 3. Architectural Patterns

To ensure clean code and separation of concerns, we follow a **Layered Architecture** approach:

### 📥 3.1 Controller Layer
Controllers are kept **slim**. Their only responsibilities are:
- Receiving requests.
- Validating input via **Form Requests**.
- Calling the appropriate **Service**.
- Returning a response via **API Resources**.

### ⚙️ 3.2 Service Layer (`app/Services`)
The "Brain" of the application. All business logic resides here.
- Example: `TournamentService->generateBracket()`, `MatchService->updateScore()`.
- Services are injected into Controllers.

### 🗄️ 3.3 Repository Layer (`app/Repositories`)
Handles data persistence and complex queries.
- Decouples the Service layer from Eloquent models.
- Ensures reusability of complex queries across different services.

### 📦 3.4 Model Layer (`app/Models`)
Eloquent models define the data structure, relationships, and basic data massaging.
- Use **Casts** for data types (e.g., JSON to Array).
- Use **Scopes** for common query filters.

### 🛡️ 3.5 Security & Authorization
- **Authentication**: Sanctum for SPA-based stateful authentication.
- **Authorization**: Laravel **Policies** to ensure users only access resources they own or have permissions for (e.g., `TournamentPolicy`).

---

## 🏗️ 4. Module Breakdown

### 👤 4.1 Identity & Access
- **Roles**: Owner, Co-Organizer, Referee, Captain, Member.
- **Auth Flow**: Cookie-based CSRF protection for the Nuxt frontend.

### 🏆 4.2 Tournament Engine
The core of the platform. Supports:
- **Single Elimination**: Standard bracket progression.
- **Double Elimination**: Upper and Lower bracket logic.
- **Round Robin**: Group-stage klasemen based on points.
- **Swiss**: Dynamic matchmaking based on W/L ratios.

### 🎮 4.3 Match Management
- **States**: `upcoming`, `ongoing`, `completed`, `cancelled`.
- **Logic**: Automated progression of winners to the next round.
- **History**: Score tracking per game (BO1, BO3, etc.).

### 👥 4.4 Team & Roster
- **Ownership**: Captains manage invitations and roster locking.
- **Verification**: Status tracking for registered participants.

---

## 📡 5. API Standards

### 🛣️ Routing
- Versioned endpoints: `/api/v1/...`
- Kebab-case URLs: `/api/v1/tournament-stages`

### 📤 Responses
Always return consistent JSON structures using **API Resources**:
```json
{
    "data": { "id": 1, "name": "Juara Cup" },
    "meta": { "total": 1 },
    "message": "Resource retrieved successfully."
}
```

### ❌ Error Handling
Standardized HTTP status codes:
- `200/201`: Success
- `400`: Bad Request (Business logic violation)
- `401/403`: Auth errors
- `422`: Validation error (returns field-specific messages)

---

## 🏗️ 7. Lean Laravel Implementation (v11/13)

Juara League leverages the **Lean Structure** of modern Laravel to minimize boilerplate and improve performance:

- **Middleware & Exceptions**: Configured via `bootstrap/app.php` instead of the traditional `Kernel.php` or `Exceptions/Handler.php`.
- **Simplified Providers**: Custom providers are only created when necessary, keeping the service container clean.
- **RESTful API Routes**: Grouped dynamically in `routes/api.php` without the overhead of the `RouteServiceProvider`.
- **Health Check**: Native `/up` endpoint monitored for production uptime.

---

## 📈 8. Database Strategy

The database is designed with high normalization to support complex tournament trees.
- **Critical Tables**: `tournaments`, `stages`, `groups`, `matches`, `participants`.
- **Indexes**: Applied to foreign keys and frequently searched columns (e.g., slug, status).
- **Referential Integrity**: Strict use of Foreign Keys and Cascades where appropriate.

> [!TIP]
> Refer to `/docs/juara-league-erd.html` for the visual entity relationship diagram.

---

## 🔒 7. Security Best Practices

- **Mass Assignment**: Always use `$fillable` or `$guarded`.
- **Rate Limiting**: Applied to all public and sensitive endpoints.
- **Data Sanitization**: Native Eloquent/PDO protection against SQL injection.
- **CORS**: Strictly configured to only allow the official frontend domain.
