
---

# 🏆 JUARA LEAGUE

### Business Requirements Document

---

## 📄 Informasi Dokumen

* **Versi:** 1.0.0 (Draft)
* **Tanggal:** 2026
* **Author:** Nandang Eka Prasetya
* **Platform:** Web App (SaaS-ready)
* **Bahasa:** Indonesia

> 🔒 Confidential – Internal Use Only

---

# 📌 1. Pendahuluan

## ▶️ 1.1 Latar Belakang

* Proses turnamen masih manual (WA, Excel)
* Tidak efisien & rawan error
* Minim transparansi

**Masalah platform existing:**

* Fokus esport
* UX kurang ramah
* Tidak lokal-friendly

---

## ▶️ 1.2 Tujuan

* Menjadi acuan:

  * Pengembangan produk
  * Desain sistem
  * Perencanaan teknis

---

## ▶️ 1.3 Ruang Lingkup

✔ Membuat turnamen
✔ Multi-format
✔ Public real-time
✔ SaaS foundation

---

## ▶️ 1.4 Definisi

Gunakan toggle di Notion untuk hemat space:

<details>
<summary>Klik untuk lihat istilah</summary>

* **Turnamen** → kumpulan stage
* **Stage** → fase turnamen
* **Match** → pertandingan
* **Game** → bagian dari match
* **BO** → Best Of
* **Seeding** → penempatan
* **Entry Fee** → biaya
* **Prize Pool** → hadiah

</details>

---

# 🚀 2. Deskripsi Produk

## 🎯 2.1 Visi

Platform turnamen paling fleksibel & mudah digunakan di Indonesia.

---

## 💡 2.2 Value Proposition

* 🧩 Sport-agnostic
* 🔀 Multi-format
* 🤖 Rekomendasi otomatis
* 🌍 Public bracket
* 👥 Multi-role
* 💰 SaaS-ready

---

## 👤 2.3 Target User

| Segmen    | Contoh            | Kebutuhan          |
| --------- | ----------------- | ------------------ |
| Komunitas | Futsal RT         | Simple & gratis    |
| Kampus    | Turnamen internal | Multi-sport        |
| Organizer | EO                | Advanced control   |
| Esport    | Tim ML/Valorant   | Competitive format |

---

# ⚙️ 3. Kebutuhan Fungsional

## 👤 3.1 Akun & Role

### ✅ Registrasi

* [ ] Email
* [ ] Google Login
* [ ] Multi-role

---

### 🧑‍💼 Role

| Role         | Akses               |
| ------------ | ------------------- |
| Owner        | Full control        |
| Co-Organizer | Manage tanpa delete |
| Referee      | Input hasil         |
| Captain      | Kelola tim          |
| Member       | View only           |
| Public       | Tanpa login         |

---

## 🏆 3.2 Manajemen Turnamen

### ➕ Create Tournament

* [ ] Nama
* [ ] Sport
* [ ] Tipe (Tim/Individu)
* [ ] Banner
* [ ] Entry fee
* [ ] Prize pool

---

### 📝 Registrasi

* [ ] Open / Invite
* [ ] Limit peserta
* [ ] Deadline
* [ ] Payment manual

---

### 🤖 Rekomendasi Format

| Kondisi     | Output            |
| ----------- | ----------------- |
| < 8         | Round Robin       |
| 8–32        | Swiss / Kombinasi |
| > 32        | Warning           |
| Swiss kecil | Tidak efisien     |

---

## 🧱 3.3 Stage

### Setup Stage

* [ ] Nama
* [ ] Format
* [ ] BO
* [ ] Peserta lolos
* [ ] Grup (opsional)

---

### Format

<details>
<summary>Jenis format</summary>

* Single Elimination
* Double Elimination
* Round Robin
* Swiss

</details>

---

## 🎮 3.4 Match

* [ ] Status (Upcoming / Ongoing / Done)
* [ ] Jadwal
* [ ] Input hasil
* [ ] Auto bracket update
* [ ] Auto advance

---

## 👥 3.5 Tim

* [ ] Create tim
* [ ] Invite member
* [ ] Register
* [ ] Transfer ownership
* [ ] Archive

---

## 🌍 3.6 Public Page

* [ ] URL shareable
* [ ] Tanpa login
* [ ] Real-time update
* [ ] Arsip

---

## 🔔 3.7 Notifikasi

* [ ] Pendaftaran
* [ ] Approval
* [ ] Jadwal
* [ ] Hasil
* [ ] Stage baru
* [ ] Selesai

---

# ⚡ 4. Non-Fungsional

## 🚀 Performa

* [ ] Load < 2 detik
* [ ] Update < 5 detik
* [ ] 1000 concurrent user

---

## 🔐 Keamanan

* [ ] JWT / Session
* [ ] Role-based access
* [ ] Enkripsi data

---

## 📈 Skalabilitas

* [ ] Payment-ready
* [ ] Modular notif
* [ ] Extendable

---

## 📱 Kompatibilitas

* [ ] Mobile friendly
* [ ] Browser modern
* [ ] SEO-friendly

---

# 📜 5. Aturan Bisnis

## 🏆 Turnamen

* [ ] Minimal 1 stage
* [ ] Stage berurutan
* [ ] Tidak bisa ubah format

---

## 👤 Peserta

* [ ] 1 akun = 1 entry
* [ ] Captain wajib daftar
* [ ] Referee tidak boleh ikut

---

## 🎮 Match

* [ ] Input oleh official
* [ ] Lock setelah submit
* [ ] BO menentukan pemenang

---

## 💰 Pembayaran

* [ ] Wajib bayar sebelum approve
* [ ] Manual
* [ ] Prize pool informatif

---

# 🚧 6. Asumsi & Batasan

## ✔️ Asumsi

* Internet stabil
* Organizer bertanggung jawab
* Fokus Indonesia

---

## ⚠️ Limit V1

* [ ] No payment gateway
* [ ] Notif terbatas
* [ ] No streaming
* [ ] No white-label

---

# 🗺️ 7. Roadmap

## 🟢 V1 – MVP

* Core tournament
* Public bracket
* Notifikasi basic

## 🟡 V2 – Growth

* Payment gateway
* WA / Telegram
* Statistik

## 🔵 V3 – SaaS

* Subscription
* API
* Analytics
* White-label

---

# ✅ Tips Pakai di Notion

Biar makin keren:

* Pakai **Toggle** untuk section panjang
* Convert checklist jadi **Database (task tracking)**
* Tambahin:

  * 🧠 Product backlog
  * 🎨 UI/UX link (Figma)
  * 📊 Metrics

---

