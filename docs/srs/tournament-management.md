# Modul 4 — Tournament Management

> **Juara League** · juaraleague.gg  
> Stack: Laravel · Nuxt.js · MySQL  
> Referensi: BRD v1.1 · ERD v1.1 · SRS Modul 4

---

## Daftar Isi

- [Overview](#overview)
- [Database Schema](#database-schema)
- [API Endpoints](#api-endpoints)
- [Alur Bisnis](#alur-bisnis)
- [State Machine](#state-machine)
- [Business Rules](#business-rules)
- [Error Codes](#error-codes)

---

## Overview

Modul Tournament Management adalah inti dari platform Juara League. Modul ini mengelola seluruh siklus hidup turnamen — dari pembuatan, konfigurasi, publikasi, pengelolaan peserta, hingga penunjukan staff.

### Ruang Lingkup

- Membuat dan mengkonfigurasi turnamen
- Edit informasi turnamen sebelum dimulai
- Publikasi turnamen (Draft → Registration)
- Mengelola pendaftaran peserta (approve, reject, invite)
- Konfirmasi pembayaran entry fee (manual)
- Menambah dan mengelola staff (Co-Organizer, Referee)
- Diskualifikasi peserta
- Hapus turnamen (sebelum dimulai)
- Halaman publik turnamen (tanpa login)
- Pencarian dan listing turnamen
- Sistem semi-auto approval untuk Free tier

---

## Database Schema

### Table: `tournaments`

| Column | Type | Nullable | Keterangan |
|--------|------|----------|------------|
| `id` | ULID | No | Primary key |
| `user_id` | ULID | No | FK → users (owner) |
| `sport_id` | ULID | No | FK → sports |
| `title` | VARCHAR(150) | No | Nama turnamen |
| `slug` | VARCHAR(170) | No | URL-friendly unique identifier |
| `description` | TEXT | Yes | Deskripsi turnamen |
| `category` | VARCHAR(100) | Yes | Kategori tambahan (opsional) |
| `status` | ENUM | No | `draft` `registration` `ongoing` `completed` |
| `approval_status` | ENUM | No | `auto_approved` `pending_review` `approved` `rejected` |
| `mode` | ENUM | No | `open` `invite` — mode registrasi |
| `bracket_type` | VARCHAR(50) | Yes | Tipe bracket (referensi ke stage format) |
| `participant_type` | ENUM | No | `individual` `team` |
| `team_size` | INT | Yes | Jumlah member per tim (jika team) |
| `max_participants` | INT | No | Batas maksimal peserta |
| `entry_fee` | DECIMAL(12,2) | No | Default 0 (gratis) |
| `prize_pool` | DECIMAL(12,2) | Yes | Total hadiah |
| `prize_description` | TEXT | Yes | Detail breakdown hadiah |
| `venue` | VARCHAR(200) | Yes | Lokasi (online/offline) |
| `banner_url` | TEXT | Yes | URL banner turnamen |
| `registration_start_at` | TIMESTAMP | Yes | Mulai buka pendaftaran |
| `registration_end_at` | TIMESTAMP | Yes | Tutup pendaftaran |
| `start_at` | TIMESTAMP | Yes | Rencana mulai turnamen |
| `created_at` | TIMESTAMP | No | |
| `updated_at` | TIMESTAMP | No | |
| `deleted_at` | TIMESTAMP | Yes | Soft delete |

> **⚠️ Field yang perlu dicek / ditambah:**
> - `approval_status` — wajib ada untuk sistem Free tier approval
> - `prize_description` — jika belum ada, tambahkan
> - Pastikan `slug` ada `UNIQUE` constraint

---

### Table: `tournament_staff`

| Column | Type | Nullable | Keterangan |
|--------|------|----------|------------|
| `id` | ULID | No | Primary key |
| `tournament_id` | ULID | No | FK → tournaments |
| `user_id` | ULID | No | FK → users |
| `role` | ENUM | No | `owner` `co_organizer` `referee` |
| `created_at` | TIMESTAMP | No | |
| `updated_at` | TIMESTAMP | No | |

---

### Table: `participants`

| Column | Type | Nullable | Keterangan |
|--------|------|----------|------------|
| `id` | ULID | No | Primary key |
| `tournament_id` | ULID | No | FK → tournaments |
| `user_id` | ULID | Yes | FK → users (jika individual) |
| `team_id` | ULID | Yes | FK → teams (jika tim) |
| `status` | ENUM | No | `pending` `approved` `rejected` `disqualified` |
| `payment_status` | ENUM | No | `free` `pending` `paid` `rejected` |
| `seed` | INT | Yes | Nomor seed peserta |
| `payment_proof_url` | TEXT | Yes | URL bukti pembayaran |
| `notes` | TEXT | Yes | Catatan organizer |
| `created_at` | TIMESTAMP | No | |
| `updated_at` | TIMESTAMP | No | |

> **⚠️ Field yang perlu ditambah di `participants`:**
> - `payment_status` — jika belum ada, pisahkan dari `status`
> - `seed` — untuk keperluan seeding sebelum stage dimulai

---

### Table: `tournament_approvals` *(baru — jika belum ada)*

| Column | Type | Nullable | Keterangan |
|--------|------|----------|------------|
| `id` | ULID | No | Primary key |
| `tournament_id` | ULID | No | FK → tournaments |
| `status` | ENUM | No | `auto_approved` `pending_review` `approved` `rejected` |
| `auto_check_log` | JSON | Yes | Hasil cek kriteria otomatis |
| `reviewed_by` | ULID | Yes | FK → users (admin yang review) |
| `reviewed_at` | TIMESTAMP | Yes | |
| `note` | TEXT | Yes | Alasan reject dari admin |
| `created_at` | TIMESTAMP | No | |

---

## API Endpoints

Base URL: `/api/v1`

| Method | Endpoint | Auth | Deskripsi |
|--------|----------|------|-----------|
| `GET` | `/tournaments` | Public | Listing turnamen publik |
| `GET` | `/tournaments/{slug}` | Public | Detail turnamen via slug |
| `POST` | `/tournaments` | Bearer | Buat turnamen baru |
| `PUT` | `/tournaments/{id}` | Bearer (Owner/Co) | Edit turnamen |
| `DELETE` | `/tournaments/{id}` | Bearer (Owner) | Hapus turnamen |
| `POST` | `/tournaments/{id}/publish` | Bearer (Owner) | Publikasikan turnamen |
| `GET` | `/tournaments/{id}/participants` | Bearer (Staff) | List peserta |
| `POST` | `/tournaments/{id}/participants/invite` | Bearer (Staff) | Invite peserta |
| `PATCH` | `/tournaments/{id}/participants/{memberId}` | Bearer (Staff) | Approve/reject/disqualify |
| `POST` | `/tournaments/{id}/participants/{memberId}/confirm-payment` | Bearer (Staff) | Konfirmasi pembayaran |
| `GET` | `/tournaments/{id}/staff` | Bearer (Owner) | List staff |
| `POST` | `/tournaments/{id}/staff/invite` | Bearer (Owner) | Undang staff |
| `DELETE` | `/tournaments/{id}/staff/{staffId}` | Bearer (Owner) | Hapus staff |
| `POST` | `/tournaments/{id}/register` | Bearer | Daftar sebagai individu |
| `POST` | `/tournaments/{id}/register-team` | Bearer (Captain) | Daftar tim |
| `DELETE` | `/tournaments/{id}/cancel-registration` | Bearer | Batalkan pendaftaran |
| `GET` | `/my/tournaments` | Bearer | Turnamen yang saya ikuti |
| `GET` | `/my/organized-tournaments` | Bearer | Turnamen yang saya buat |

---

## Request & Response

### POST `/tournaments` — Buat Turnamen

**Request Body** `multipart/form-data`:
```
title           : string (required, min:3, max:150)
sport_id        : ULID (required)
participant_type: individual|team (required)
mode            : open|invite (required)
max_participants: integer (required, min:2, max:512)
entry_fee       : decimal (optional, default:0)
prize_pool      : decimal (optional)
prize_description: string (optional, max:1000)
description     : string (optional, max:2000)
banner          : file (optional, jpg|png|webp, max:5MB)
registration_end_at: datetime (optional)
team_size       : integer (required if participant_type=team)
```

**Response 201:**
```json
{
  "message": "Turnamen berhasil dibuat.",
  "data": {
    "id": "01JXXX",
    "title": "Turnamen ML Nasional 2026",
    "slug": "turnamen-ml-nasional-2026",
    "status": "draft",
    "approval_status": "auto_approved",
    "participant_type": "team",
    "mode": "open",
    "max_participants": 32,
    "entry_fee": 50000,
    "sport": { "id": "01JXXX", "name": "Mobile Legends" },
    "created_at": "2026-01-01T00:00:00Z"
  }
}
```

---

### GET `/tournaments/{slug}` — Detail Turnamen

**Response 200:**
```json
{
  "data": {
    "id": "01JXXX",
    "title": "Turnamen ML Nasional 2026",
    "slug": "turnamen-ml-nasional-2026",
    "status": "registration",
    "approval_status": "approved",
    "participant_type": "team",
    "mode": "open",
    "max_participants": 32,
    "participants_count": 12,
    "entry_fee": 50000,
    "prize_pool": 1000000,
    "prize_description": "Juara 1: Rp 600rb | Juara 2: Rp 250rb | Juara 3: Rp 150rb",
    "sport": { "id": "01JXXX", "name": "Mobile Legends" },
    "banner_url": "https://r2.juaraleague.gg/banners/xxx.jpg",
    "registration_end_at": "2026-06-01T23:59:59Z",
    "stages": [
      { "id": "01JXXX", "name": "Group Stage", "type": "round_robin", "status": "pending" }
    ],
    "created_at": "2026-01-01T00:00:00Z"
  }
}
```

---

### PATCH `/tournaments/{id}/participants/{memberId}` — Update Status Peserta

**Request Body:**
```json
{
  "status": "approved",
  "notes": "Pembayaran sudah dikonfirmasi"
}
```

**Response 200:**
```json
{
  "message": "Status peserta berhasil diperbarui.",
  "data": {
    "id": "01JXXX",
    "status": "approved"
  }
}
```

---

## Alur Bisnis

### Alur Organizer — Buat Turnamen (Free Tier)

```
Organizer buat turnamen
        ↓
Sistem cek kuota bulanan (< 5?)
  └── Tidak → ERROR: quota_exceeded
        ↓
Sistem jalankan auto-approve check:
  ✅ Email verified
  ✅ Akun > 7 hari
  ✅ Nama tidak mengandung kata terlarang
  ✅ Akun tidak suspended
        ↓
Semua lolos?
  ✅ Ya  → approval_status: auto_approved → status: draft
  ❌ Ada yang gagal → approval_status: pending_review
        ↓ (jika pending_review)
Admin review
  → Approved → status: draft → notifikasi ke organizer
  → Rejected → notifikasi + alasan → organizer bisa revisi
```

### Alur Organizer — Buat Turnamen (Pro Tier)

```
Organizer buat turnamen
        ↓
Sistem cek subscription Pro aktif
        ↓
Skip approval → langsung status: draft
```

### Alur Peserta — Daftar Turnamen (Open, Berbayar)

```
Peserta klik Daftar
        ↓
Sistem validasi: kuota belum penuh, belum pernah daftar
        ↓
Record participants dibuat:
  status: pending
  payment_status: pending
        ↓
Peserta upload bukti pembayaran
        ↓
Organizer verifikasi & konfirmasi pembayaran
        ↓
payment_status: paid
status: approved
        ↓
Notifikasi ke peserta
```

### Alur Peserta — Daftar Turnamen (Invite Only)

```
Organizer cari user/tim
        ↓
Organizer tambahkan langsung
        ↓
Record participants dibuat:
  status: approved (langsung)
  payment_status: free / pending (tergantung entry_fee)
        ↓
Notifikasi undangan ke peserta
```

---

## State Machine

### Status Tournament

```
draft ──────────────────→ registration ──→ ongoing ──→ completed
  │                            │
  └── (delete) ──→ deleted     └── (delete) → TIDAK BISA
```

| Status | Deskripsi | Aksi Tersedia |
|--------|-----------|---------------|
| `draft` | Baru dibuat, belum dipublikasikan | Edit, tambah stage, hapus, publikasi |
| `registration` | Aktif menerima pendaftaran | Edit info dasar, kelola peserta, mulai stage |
| `ongoing` | Stage pertama sudah dimulai | Input hasil match, diskualifikasi peserta |
| `completed` | Semua stage selesai | Read-only, arsip publik |

### Status Approval Tournament

| Status | Set By | Kondisi |
|--------|--------|---------|
| `auto_approved` | Sistem | Semua kriteria auto-check terpenuhi |
| `pending_review` | Sistem | Ada kriteria auto-check yang gagal |
| `approved` | Admin | Review manual: disetujui |
| `rejected` | Admin | Review manual: ditolak |

### Status Peserta

| Status | Deskripsi |
|--------|-----------|
| `pending` | Mendaftar, menunggu approval organizer |
| `approved` | Diterima sebagai peserta |
| `rejected` | Ditolak organizer |
| `disqualified` | Didiskualifikasi saat turnamen berjalan |

### Status Pembayaran Peserta

| Status | Deskripsi |
|--------|-----------|
| `free` | Turnamen gratis |
| `pending` | Sudah daftar, belum bayar / belum konfirmasi |
| `paid` | Pembayaran dikonfirmasi organizer |
| `rejected` | Bukti pembayaran ditolak |

---

## Business Rules

| # | Rule |
|---|------|
| BR01 | Turnamen wajib punya minimal 1 stage sebelum bisa dipublikasikan |
| BR02 | `participant_type` tidak bisa diubah setelah ada peserta yang `approved` |
| BR03 | `sport_id` tidak bisa diubah setelah turnamen dipublikasikan |
| BR04 | Jumlah peserta `approved` tidak boleh melebihi `max_participants` |
| BR05 | Untuk turnamen berbayar, `payment_status` harus `paid` sebelum `status` bisa jadi `approved` |
| BR06 | Referee tidak boleh terdaftar sebagai peserta di turnamen yang sama |
| BR07 | Owner/Co-Organizer tidak bisa mendaftar sebagai peserta di turnamen sendiri |
| BR08 | Peserta `disqualified` tidak bisa didaftarkan ulang ke turnamen yang sama |
| BR09 | `slug` harus unik — jika ada duplikat, sistem tambahkan suffix `-2`, `-3`, dst |
| BR10 | Turnamen `draft` tidak muncul di listing publik |
| BR11 | Free user maksimal 5 turnamen per bulan kalender |
| BR12 | Turnamen `ongoing` atau `completed` tidak bisa dihapus |

---

## Error Codes

| HTTP | Error Code | Message | Trigger |
|------|-----------|---------|---------|
| 404 | `TOURNAMENT_NOT_FOUND` | Turnamen tidak ditemukan. | Slug/ID tidak ada |
| 403 | `NOT_TOURNAMENT_STAFF` | Anda bukan staff turnamen ini. | Non-staff akses endpoint staff |
| 403 | `NOT_TOURNAMENT_OWNER` | Hanya Owner yang dapat melakukan ini. | Non-owner akses endpoint owner |
| 409 | `TOURNAMENT_ONGOING` | Turnamen sedang berjalan. | Hapus turnamen ongoing |
| 409 | `QUOTA_FULL` | Kuota peserta sudah penuh. | Daftar ke turnamen full |
| 409 | `ALREADY_REGISTERED` | Anda sudah terdaftar di turnamen ini. | Daftar dua kali |
| 400 | `NO_STAGE_CONFIGURED` | Minimal satu stage harus dikonfigurasi. | Publikasi tanpa stage |
| 400 | `INSUFFICIENT_PARTICIPANTS` | Minimal 2 peserta approved untuk mulai. | Start stage tanpa peserta |
| 409 | `REFEREE_IS_PARTICIPANT` | Referee tidak bisa menjadi peserta. | Conflict role |
| 409 | `ORGANIZER_CANNOT_REGISTER` | Organizer tidak bisa mendaftar sebagai peserta. | Owner daftar ke turnamen sendiri |
| 400 | `PAYMENT_NOT_CONFIRMED` | Pembayaran belum dikonfirmasi. | Approve peserta yang belum bayar |
| 429 | `QUOTA_EXCEEDED` | Kuota turnamen bulanan habis. Upgrade ke Pro. | Free user buat turnamen ke-6 |
| 403 | `TOURNAMENT_PENDING_REVIEW` | Turnamen masih menunggu review platform. | Publikasi saat pending_review |
| 403 | `TOURNAMENT_REJECTED` | Turnamen ditolak. Lihat alasan penolakan. | Publikasi turnamen rejected |
| 422 | `VALIDATION_ERROR` | Field {field} tidak valid. | Input tidak valid |

---

## Catatan Implementasi

### Approval Service

Buat `TournamentApprovalService` yang dipanggil setelah tournament dibuat:

```php
class TournamentApprovalService
{
    public function evaluate(Tournament $tournament): string
    {
        // Cek tier user
        if ($tournament->user->plan === 'pro') {
            return $this->autoApprove($tournament);
        }

        // Cek kuota bulanan
        if ($this->isQuotaExceeded($tournament->user)) {
            throw new QuotaExceededException();
        }

        // Jalankan auto-check criteria
        $checks = $this->runAutoChecks($tournament);
        $allPassed = collect($checks)->every(fn($c) => $c['passed']);

        if ($allPassed) {
            return $this->autoApprove($tournament);
        }

        return $this->sendToReview($tournament, $checks);
    }

    private function runAutoChecks(Tournament $tournament): array
    {
        $user = $tournament->user;
        return [
            'email_verified'   => ['passed' => !is_null($user->email_verified_at)],
            'account_age'      => ['passed' => $user->created_at->diffInDays() >= 7],
            'no_banned_words'  => ['passed' => $this->checkBannedWords($tournament->title)],
            'not_suspended'    => ['passed' => !$user->is_suspended],
        ];
    }
}
```

### Slug Generator

```php
public static function generateSlug(string $title): string
{
    $slug = Str::slug($title);
    $count = Tournament::where('slug', 'LIKE', "{$slug}%")->count();
    return $count > 0 ? "{$slug}-{$count}" : $slug;
}
```

### Policy

```php
// TournamentPolicy.php
public function update(User $user, Tournament $tournament): bool
{
    return $tournament->user_id === $user->id
        || $tournament->staff()->where('user_id', $user->id)
            ->whereIn('role', ['co_organizer'])->exists();
}

public function publish(User $user, Tournament $tournament): bool
{
    return $tournament->user_id === $user->id
        && $tournament->approval_status !== 'rejected'
        && $tournament->approval_status !== 'pending_review';
}
```

---

*Dokumen ini adalah bagian dari dokumentasi teknis Juara League.*  
*Last updated: 2026*
