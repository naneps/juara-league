<div align="center">
  <img src="https://raw.githubusercontent.com/lucide-icons/lucide/main/icons/trophy.svg" alt="Trophy Icon" width="80" height="80">
  <h1 align="center">Juara League Platform</h1>
  
  <p align="center">
    <strong>The most flexible, multi-format, and easy-to-use tournament management platform in Indonesia.</strong>
  </p>

  <p align="center">
    <i>Built with Nuxt 4, Nuxt UI, and Laravel 11/13</i>
  </p>
</div>

---

## 📖 Table of Contents
- [About the Project](#-about-the-project)
- [Project Architecture](#-project-architecture)
- [Technical Stack](#-technical-stack)
- [Core Features (MVP)](#-core-features-mvp)
- [Getting Started (Local Development)](#-getting-started-local-development)
  - [Prerequisites](#prerequisites)
  - [1. Backend Setup (Laravel API)](#1-backend-setup-laravel-api)
  - [2. Frontend Setup (Nuxt SPA)](#2-frontend-setup-nuxt-spa)
- [Documentation](#-documentation)

---

## 🏆 About the Project

**Juara League** is a SaaS-ready platform designed to streamline tournament management. Unlike traditional platforms that focus strictly on esports or have rigid setups, Juara League is sports-agnostic, supporting everything from local futsal communities to major multi-stage competitions. 

The platform emphasizes transparency and real-time updates via public, shareable brackets, multi-role management, and automated matchmaking recommendations based on entrant sizes.

---

## 📂 Project Architecture

This repository adopts a decoupled, headless architecture:

```text
juara-league/
├── juara-league-api/   # Laravel 11+ Backend Services & RESTful APIs
├── juara-league-web/   # Nuxt 4 + Nuxt UI (Vue.js) Frontend SPA
└── docs/               # Technical documents, BRD, Flowcharts, and ERD
```

---

## 🛠 Technical Stack

### Backend (`/juara-league-api`)
- **Framework**: Laravel 11+
- **Database**: SQLite (Development) / PostgreSQL (Production)
- **Authentication**: Laravel Sanctum (Stateful Cookie-Based Auth)
- **API Standard**: RESTful JSON API

### Frontend (`/juara-league-web`)
- **Framework**: Nuxt 4 (Vue.js SSR & SPA capabilities)
- **UI Library**: Nuxt UI v4 (Tailwind CSS 4 & Headless UI components)
- **Icons**: Iconify (via `@nuxt/icon`)
- **Package Manager**: npm

---

## 🚀 Core Features (MVP)

1. **Flexible Tournament Formats**  
   Support for Single Elimination, Double Elimination, Round Robin, and Swiss formats in a single platform.
2. **Multi-Role User Ecosystem**  
   Granular access controls including `Owner`, `Co-Organizer`, `Referee`, `Captain`, and `Member`.
3. **Public Brackets**  
   Shareable URLs that allow anyone to view live tournament progress without requiring login verification.
4. **Team & Roster Management**  
   Captains can create teams, invite members, handle registrations, and transfer ownership seamlessly.

---

## 💻 Getting Started (Local Development)

To run this project locally, you will need to start both the backend API and the frontend application concurrently.

### Prerequisites
- **PHP**: 8.3 or higher
- **Composer**: 2.x
- **Node.js**: v20 LTS or higher
- **NPM**: 10.x or higher

### 1. Backend Setup (Laravel API)

Open a terminal and navigate to the backend directory:

```bash
cd juara-league-api

# Install PHP dependencies
composer install

# Duplicate the environment configuration file
cp .env.example .env

# Generate application security key
php artisan key:generate

# Run initial database migrations
php artisan migrate

# Start the local development server (defaults to port 8000)
php artisan serve
```

> [!IMPORTANT]
> **Sanctum Configuration**  
> For the SPA authentication to work correctly across the local environment, ensure your `.env` contains the following stateful configurations:
> ```env
> APP_URL=http://localhost:8000
> FRONTEND_URL=http://localhost:3000
> SESSION_DOMAIN=localhost
> SANCTUM_STATEFUL_DOMAINS=localhost:3000
> ```

### 2. Frontend Setup (Nuxt SPA)

Open a **new** terminal window and navigate to the frontend directory:

```bash
cd juara-league-web

# Install JavaScript dependencies
npm install

# Start the Nuxt development server
npm run dev
```

The application interface will now be accessible at [http://localhost:3000](http://localhost:3000).

---

## 📄 Documentation

For an in-depth understanding of the business logic, edge cases, and future roadmaps, please explore the `/docs` directory.

Key documents:
- [docs/business/BRD.md](file:///d:/PROJECT/juara-league/docs/business/BRD.md): Full Business Requirements Document detailing scopes and user segments.
- [docs/business/USECASEs.md](file:///d:/PROJECT/juara-league/docs/business/USECASEs.md): Core system behaviors and actor lifecycles.
- [docs/technical/REQUIREMENTS.md](file:///d:/PROJECT/juara-league/docs/technical/REQUIREMENTS.md): Detailed server hardware and software specifications.
- [docs/technical/DEPLOYMENT.md](file:///d:/PROJECT/juara-league/docs/technical/DEPLOYMENT.md): Step-by-step production setup and orchestration guide.
- Explore `docs/srs/` and `docs/design/` for SRS modules and architecture diagrams.