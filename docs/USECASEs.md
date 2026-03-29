
---

# 🏆 JUARA LEAGUE

## Use Case Document

---

## 📄 Informasi Dokumen

* **Versi:** 1.0.0 (Draft)
* **Tanggal:** 2026
* **Author:** Nandang Eka Prasetya
* **Referensi:** BRD v1.0.0

> 🔒 Confidential – Internal Use Only

---

# 📌 1. Pendahuluan

## ▶️ 1.1 Tujuan

Dokumen ini mendefinisikan:

* Interaksi aktor dengan sistem
* Acuan development
* Acuan testing (QA)

---

## ▶️ 1.2 Aktor

| Aktor         | Deskripsi        | Login |
| ------------- | ---------------- | ----- |
| Guest         | Belum punya akun | ❌     |
| Public Viewer | Lihat publik     | ❌     |
| User          | Base user        | ✅     |
| Owner         | Pembuat turnamen | ✅     |
| Co-Organizer  | Staff            | ✅     |
| Referee       | Input hasil      | ✅     |
| Captain       | Owner tim        | ✅     |
| Member        | Anggota tim      | ✅     |

---

## ▶️ 1.3 Daftar Use Case (Checklist Ready 🚀)

### 🔐 Akun

* [ ] UC-001 Registrasi
* [ ] UC-002 Login
* [ ] UC-003 Edit Profil
* [ ] UC-004 Ganti Password
* [ ] UC-005 Logout

### 🏆 Turnamen

* [ ] UC-006 Buat Turnamen
* [ ] UC-007 Edit Turnamen
* [ ] UC-008 Hapus Turnamen
* [ ] UC-009 Tambah Stage
* [ ] UC-010 Publish Turnamen

### 👥 Peserta

* [ ] UC-011 Kelola Pendaftaran
* [ ] UC-012 Konfirmasi Pembayaran
* [ ] UC-013 Diskualifikasi

### 🧑‍💼 Staff & Match

* [ ] UC-014 Tambah Staff
* [ ] UC-015 Set Jadwal
* [ ] UC-016 Mulai Stage
* [ ] UC-017 Input Hasil
* [ ] UC-018 Koreksi Hasil
* [ ] UC-019 Advance Stage

### 👤 User / Tim

* [ ] UC-020 Daftar Individual
* [ ] UC-021 Buat Tim
* [ ] UC-022 Daftar Tim
* [ ] UC-023 Invite Member
* [ ] UC-024 Remove Member
* [ ] UC-025 Keluar Tim
* [ ] UC-026 Transfer Captain
* [ ] UC-027 Dashboard Peserta

### 🔔 & Public

* [ ] UC-028 Notifikasi
* [ ] UC-029 Lihat Bracket
* [ ] UC-030 Arsip

---

# ⚙️ 2. Detail Use Case (Toggle Style)

---

## 🔐 2.1 Manajemen Akun

<details>
<summary><b>UC-001 — Registrasi</b></summary>

**Aktor:** Guest

**Flow:**

1. Buka halaman registrasi
2. Pilih email / Google
3. Validasi
4. Verifikasi email
5. Masuk dashboard

**Alt:**

* Email sudah ada → login
* Link expired → resend

✅ Output: akun aktif

</details>

---

<details>
<summary><b>UC-002 — Login</b></summary>

**Flow:**

1. Input email/password
2. Validasi
3. Masuk dashboard

**Alt:**

* Salah → error
* Belum verif → diminta verif

</details>

---

<details>
<summary><b>UC-003 — Edit Profil</b></summary>

* Edit nama / foto
* Save

</details>

---

<details>
<summary><b>UC-004 — Ganti Password</b></summary>

* Input lama + baru
* Validasi

</details>

---

<details>
<summary><b>UC-005 — Logout</b></summary>

* Klik logout
* Session selesai

</details>

---

# 🏆 2.2 Organizer — Turnamen

---

<details>
<summary><b>UC-006 — Buat Turnamen</b></summary>

**Flow:**

1. Isi data
2. Set registrasi
3. Save

✅ Output:

* Status Draft
* URL publik

</details>

---

<details>
<summary><b>UC-007 — Edit Turnamen</b></summary>

* Edit data sebelum mulai

</details>

---

<details>
<summary><b>UC-008 — Hapus Turnamen</b></summary>

* Hanya sebelum mulai

</details>

---

<details>
<summary><b>UC-009 — Tambah Stage</b></summary>

* Pilih format
* Set BO
* Set peserta lolos

⚠️ Ada warning sistem

</details>

---

<details>
<summary><b>UC-010 — Publish</b></summary>

* Draft → Registration
* Wajib ada stage

</details>

---

<details>
<summary><b>UC-011 — Kelola Pendaftaran</b></summary>

* Approve / Reject
* Invite manual

</details>

---

<details>
<summary><b>UC-012 — Konfirmasi Pembayaran</b></summary>

* Verifikasi manual
* Set status → Paid

</details>

---

<details>
<summary><b>UC-013 — Diskualifikasi</b></summary>

* Remove dari bracket

</details>

---

# 🎮 2.3 Match & Stage

---

<details>
<summary><b>UC-014 — Tambah Staff</b></summary>

* Invite user
* Set role

</details>

---

<details>
<summary><b>UC-015 — Set Jadwal</b></summary>

* Set tanggal
* Kirim notif

</details>

---

<details>
<summary><b>UC-016 — Mulai Stage</b></summary>

* Generate bracket
* Status → Ongoing

</details>

---

<details>
<summary><b>UC-017 — Input Hasil</b></summary>

* Input skor
* Auto update
* Auto advance

</details>

---

<details>
<summary><b>UC-018 — Koreksi Hasil</b></summary>

* Owner only

</details>

---

<details>
<summary><b>UC-019 — Advance Stage</b></summary>

* Ambil top peserta
* Generate stage baru

</details>

---

# 👥 2.4 Tim & Peserta

---

<details>
<summary><b>UC-020 — Daftar Individual</b></summary>

* Klik daftar
* Tunggu approval

</details>

---

<details>
<summary><b>UC-021 — Buat Tim</b></summary>

* Nama + logo

</details>

---

<details>
<summary><b>UC-022 — Daftar Tim</b></summary>

* Pilih tim
* Submit

</details>

---

<details>
<summary><b>UC-023 — Invite Member</b></summary>

* Invite via email

</details>

---

<details>
<summary><b>UC-024 — Remove Member</b></summary>

* Hapus dari roster

</details>

---

<details>
<summary><b>UC-025 — Keluar Tim</b></summary>

* Member keluar

</details>

---

<details>
<summary><b>UC-026 — Transfer Captain</b></summary>

* Pindah ownership

</details>

---

<details>
<summary><b>UC-027 — Dashboard Peserta</b></summary>

* Lihat jadwal
* Lihat hasil

</details>

---

# 🔔 2.5 Notifikasi

<details>
<summary><b>UC-028 — Notifikasi</b></summary>

* Lihat
* Klik → redirect
* Mark as read

</details>

---

# 🌍 2.6 Public

---

<details>
<summary><b>UC-029 — Lihat Bracket</b></summary>

* Tanpa login
* Real-time

</details>

---

<details>
<summary><b>UC-030 — Arsip</b></summary>

* Lihat hasil akhir

</details>

---

# 🔗 3. Relasi Use Case (Simplified)

* UC-006 → UC-009 (buat → tambah stage)
* UC-006 → UC-010 (buat → publish)
* UC-011 → UC-016 (peserta → mulai stage)
* UC-017 → UC-019 (hasil → next stage)
* UC-021 → UC-022 (buat tim → daftar)
* UC-017 → UC-018 (input → koreksi)

---

# ✅ Cara Pakai di Notion (Best Practice)

Biar makin powerful:

### 🔧 Convert jadi Database

* Table: **Use Case Tracker**

  * ID
  * Nama
  * Status (Not Started / Dev / Done)
  * Priority
  * Owner (Dev)

---

### 🧠 Tambahin Section:

* 🎯 MVP Scope (filter UC yang masuk V1)
* 🐞 QA Test Case (link ke UC)
* 🎨 Figma link per UC

---
