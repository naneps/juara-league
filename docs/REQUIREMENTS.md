# Server Requirements

This document specifies the minimum and recommended technical requirements for hosting the Juara League platform in a production environment.

## 🖥 1. Operating System

We recommend using a stable, long-term support (LTS) Linux distribution:
- **Recommended**: Ubuntu 22.04 LTS or 24.04 LTS
- **Supported**: Debian 11/12, RHEL 8/9, CentOS Stream 9

## ⚙ 2. Hardware Recommendations

| Component | Minimum (Small Scale) | Recommended (Medium Scale) |
| :--- | :--- | :--- |
| **CPU** | 1 Core (2.0 GHz+) | 2-4 Cores |
| **RAM** | 2 GB | 4-8 GB |
| **Disk Space** | 20 GB (SSD/NVMe) | 50 GB+ |
| **Bandwidth** | 100 Mbps | 1 Gbps |

*Note: RAM is the most critical factor for concurrent Nuxt SSR and Laravel PHP-FPM processes.*

---

## 🛠 3. Software Stack

### 3.1 PHP 8.3+
The backend requires PHP 8.3 or higher. Ensure the following extensions are installed:
- `ctype`, `curl`, `dom`, `fileinfo`, `filter`, `hash`, `iconv`, `intl`, `json`, `libxml`, `mbstring`, `openssl`, `pcre`, `pdo`, `pdo_pgsql`, `session`, `tokenizer`, `xml`, `xmlwriter`, `zip`, `zlib`.
- **Recommended**: `opcache` (enabled), `bcmath`, `redis`.

### 3.2 Node.js 20+ (LTS)
The frontend requires Node.js v20 minimum.
- **Package Manager**: NPM 10+ or Yarn.

### 3.3 Database
- **PostgreSQL 15+** (Production recommendation).
- SQLite is only for local development and testing.

### 3.4 Web Server & Process Management
- **Nginx**: 1.22+ (Main reverse proxy).
- **PM2**: Latest version (For Nuxt process management).
- **Supervisor**: Latest version (For Laravel queue workers).

---

## 🔒 4. Network & Security

### 4.1 Required Ports
| Port | Protocol | Description |
| :--- | :--- | :--- |
| **22** | TCP | SSH Access |
| **80** | TCP | HTTP (Redirect to HTTPS) |
| **443** | TCP | HTTPS (SSL/TLS required) |
| **5432** | TCP | PostgreSQL (Internal only, should not be public) |

### 4.2 SSL Certificates
A valid SSL/TLS certificate is **mandatory** for production to ensure secure cookie-based authentication (Sanctum).
- **Recommended**: Let's Encrypt (via Certbot).

---

## 📦 5. External Services (Optional)

The platform can be integrated with these services for better scalability:
- **Redis**: For session and cache driver.
- **S3 Compatible Storage**: For storing public logos and user avatars (AWS S3, DigitalOcean Spaces, MinIO).
- **Google OAuth**: Required for "Login with Google" feature.

---

> [!CAUTION]
> **SWAP Space**  
> If you are using a server with 2 GB of RAM or less, we **strongly recommend** creating at least a 2 GB SWAP file to prevent out-of-memory (OOM) errors during `npm run build`.
