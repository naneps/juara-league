
---

# JUARA LEAGUE | Business Requirements Document

**JUARA LEAGUE**
juaraleague.gg

## BUSINESS REQUIREMENTS DOCUMENT

**General-Purpose Tournament Management Platform**

| Field       | Value                        |
| ----------- | ---------------------------- |
| Versi       | 1.0.0 — Draft                |
| Tanggal     | 2026                         |
| Status      | Draft                        |
| Dibuat oleh | Nandang Eka Prasetya         |
| Platform    | Web Application (SaaS-ready) |
| Bahasa      | Bahasa Indonesia             |

> Dokumen ini bersifat konfidensial dan hanya untuk penggunaan internal.

---

# 1. Pendahuluan

## 1.1 Latar Belakang

Penyelenggaraan turnamen olahraga dan esport di Indonesia, khususnya pada level komunitas, saat ini masih sangat bergantung pada proses manual — koordinasi via grup WhatsApp, pencatatan hasil di Excel, dan pengumuman bracket secara manual.

Hal ini menyebabkan proses yang tidak efisien, rawan kesalahan, dan minim transparansi bagi peserta maupun penonton.

Platform global seperti **Challonge**, **Toornament**, dan **Start.gg** mayoritas berfokus pada esport dan memiliki UX yang kurang ramah untuk pengguna baru.

Belum ada platform yang benar-benar general-purpose, mudah digunakan, dan relevan untuk pasar Indonesia.

---

## 1.2 Tujuan Dokumen

Dokumen ini mendeskripsikan kebutuhan bisnis dari platform **Juara League** — platform manajemen turnamen berbasis web yang bersifat:

* sport-agnostic
* multi-format
* multi-role

Dokumen ini menjadi acuan utama untuk:

* pengembangan produk
* desain sistem
* perencanaan teknis

---

## 1.3 Ruang Lingkup

Juara League memungkinkan pengguna untuk:

* Membuat dan mengelola turnamen berbagai cabang olahraga dan esport
* Mendukung berbagai format turnamen secara fleksibel
* Memberikan akses publik real-time

Dirancang sebagai fondasi produk **SaaS scalable**.

---

## 1.4 Definisi & Istilah

| Istilah       | Definisi                                     |
| ------------- | -------------------------------------------- |
| Turnamen      | Kompetisi terdiri dari satu atau lebih stage |
| Stage         | Fase/babak dalam turnamen                    |
| Match         | Pertandingan antara dua peserta/tim          |
| Game          | Satu sesi permainan dalam match              |
| Grup          | Pembagian peserta (Round Robin)              |
| BO (Best Of)  | Sistem jumlah kemenangan                     |
| Organizer     | Pembuat turnamen                             |
| Peserta       | Individu/tim yang ikut                       |
| Captain       | Perwakilan tim                               |
| Public Viewer | Pengguna tanpa login                         |
| Seeding       | Penempatan berdasarkan ranking               |
| Entry Fee     | Biaya pendaftaran                            |
| Prize Pool    | Total hadiah                                 |

---

# 2. Deskripsi Produk

## 2.1 Visi Produk

Menjadi platform manajemen turnamen paling fleksibel dan mudah digunakan di Indonesia.

---

## 2.2 Proposisi Nilai

**Keunggulan Juara League:**

* Sport-agnostic
* Multi-format (SE, DE, RR, Swiss)
* Rekomendasi format otomatis
* Public bracket
* Multi-role
* SaaS-ready

---

## 2.3 Target Pengguna

| Segmen           | Contoh               | Kebutuhan         |
| ---------------- | -------------------- | ----------------- |
| Komunitas lokal  | Futsal RT, badminton | Mudah, gratis     |
| Kampus           | Turnamen esport      | Multi-sport       |
| Organizer        | EO lokal             | Manajemen lengkap |
| Komunitas esport | ML, Valorant         | Format kompleks   |

---

# 3. Kebutuhan Fungsional

## 3.1 Manajemen Akun & Role

### 3.1.1 Registrasi & Login

* Email
* Google OAuth
* Multi-role per turnamen

---

### 3.1.2 Role dalam Turnamen

| Role          | Cakupan  | Keterangan          |
| ------------- | -------- | ------------------- |
| Owner         | Turnamen | Full akses          |
| Co-Organizer  | Turnamen | Manage tanpa delete |
| Referee       | Turnamen | Input hasil         |
| Captain       | Tim      | Daftarkan tim       |
| Member        | Tim      | View only           |
| Public Viewer | Global   | Tanpa login         |

---

## 3.2 Manajemen Turnamen

### 3.2.1 Pembuatan Turnamen

Field:

* Nama
* Sport
* Tipe peserta
* Deskripsi
* Banner
* Entry fee
* Prize pool

---

### 3.2.2 Konfigurasi Registrasi

* Open / Invite-only
* Limit peserta
* Deadline
* Pembayaran manual

---

### 3.2.3 Sistem Rekomendasi Format

| Kondisi   | Rekomendasi  |
| --------- | ------------ |
| < 8       | Round Robin  |
| 8–32      | Semua format |
| > 32 RR   | Warning      |
| Swiss < 6 | Warning      |

---

## 3.3 Manajemen Stage

### 3.3.1 Struktur

* Nama stage
* Format
* BO
* Jumlah lolos
* Grup (opsional)

---

### 3.3.2 Format

| Format             | Deskripsi                |
| ------------------ | ------------------------ |
| Single Elimination | Gugur sekali             |
| Double Elimination | Gugur 2x                 |
| Round Robin        | Semua vs semua           |
| Swiss              | Pairing berdasarkan poin |

---

## 3.4 Manajemen Match

* Status: Upcoming / Ongoing / Completed
* Jadwal opsional
* Input per game
* Auto update bracket

---

## 3.5 Manajemen Tim

* Captain buat tim
* Invite member
* Transfer ownership
* Tidak bisa delete (archive)

---

## 3.6 Halaman Publik

* URL publik
* Tanpa login
* Real-time bracket

---

## 3.7 Notifikasi

| Trigger     | Penerima  | Channel       |
| ----------- | --------- | ------------- |
| Pendaftaran | Organizer | Email, In-app |
| Disetujui   | Peserta   | Email         |
| Jadwal      | Peserta   | Email, WA     |
| Hasil       | Peserta   | In-app        |

---

# 4. Kebutuhan Non-Fungsional

## 4.1 Performa

* Load < 2 detik
* Update < 5 detik
* 1000 concurrent

---

## 4.2 Keamanan

* JWT/Auth
* Role-based access
* Enkripsi

---

## 4.3 Skalabilitas

* Payment-ready
* Modular notification

---

## 4.4 Kompatibilitas

* Browser modern
* Mobile responsive
* SEO-friendly

---

# 5. Aturan Bisnis

## 5.1 Turnamen

* Minimal 1 stage
* Stage berurutan
* Tidak bisa ubah format setelah start

---

## 5.2 Peserta & Tim

* 1 akun = 1 entitas
* Captain wajib
* Tidak bisa delete saat aktif

---

## 5.3 Match

* Input oleh role tertentu
* Tidak bisa diubah (kecuali Owner)

---

## 5.4 Pembayaran

* Manual
* Prize pool informatif

---

# 6. Asumsi & Batasan

## 6.1 Asumsi

* Internet stabil
* Organizer bertanggung jawab
* Fokus Indonesia

---

## 6.2 Batasan V1

* No payment gateway
* No WA notif (awal)
* No streaming

---

# 7. Model Monetisasi

## 7.1 Tier

| Fitur    | Free  | Pro       |
| -------- | ----- | --------- |
| Turnamen | 5     | Unlimited |
| Approval | Delay | Instan    |
| Branding | Ya    | Custom    |
| Support  | No    | Yes       |

---

## 7.2 Subscription

* Bulanan / tahunan
* Auto downgrade

---

## 7.3 Approval System

**Auto-approve jika:**

* Email verified
* Akun > 7 hari
* Tidak melanggar aturan

---

## Status

| Status         | Deskripsi      |
| -------------- | -------------- |
| auto_approved  | Langsung draft |
| pending_review | Review manual  |
| approved       | Disetujui      |
| rejected       | Ditolak        |

---

# 8. Roadmap Produk

| Fase | Fitur              |
| ---- | ------------------ |
| V1   | MVP                |
| V2   | Payment + Pro      |
| V3   | Scale + Enterprise |

---

**— End of Document —**
*Confidential - Internal Use Only*

---

