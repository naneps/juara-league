# JUARA LEAGUE

*juaraleague.gg*

## Use Case Document

*General-Purpose Tournament Management Platform*

| Field           | Nilai                   |
| --------------- | ----------------------- |
| **Versi**       | 1.0.0 — Draft           |
| **Tanggal**     | 2026                    |
| **Status**      | Draft                   |
| **Dibuat oleh** | Nandang Eka Prasetya    |
| **Referensi**   | BRD Juara League v1.0.0 |

*Dokumen ini bersifat konfidensial dan hanya untuk penggunaan internal.*

---

## 1. Pendahuluan

### 1.1 Tujuan Dokumen

Dokumen ini mendeskripsikan seluruh Use Case dari platform Juara League — mendefinisikan interaksi antara aktor dengan sistem untuk mencapai tujuan tertentu. Dokumen ini menjadi acuan dalam perancangan sistem, pengembangan fitur, dan pengujian aplikasi.

### 1.2 Daftar Aktor

| Aktor          | Deskripsi                                                        | Login? |
| -------------- | ---------------------------------------------------------------- | ------ |
| Guest          | Pengguna yang belum memiliki akun.                               | Tidak  |
| Public Viewer  | Mengakses halaman publik turnamen tanpa login.                   | Tidak  |
| User           | Pengguna dengan akun. Basis dari semua role.                     | Ya     |
| Owner          | User yang membuat turnamen. Akses penuh.                         | Ya     |
| Co-Organizer   | Diundang Owner untuk membantu mengelola.                         | Ya     |
| Referee        | Hanya dapat menginput hasil match.                               | Ya     |
| Captain        | Pemilik tim, berwenang daftarkan tim ke turnamen.                | Ya     |
| Admin Platform | User dengan `is_admin = true`. Mengelola platform dari `/admin`. | Ya     |

### 1.3 Ringkasan Use Case

| ID     | Nama Use Case                     | Aktor Utama                  |
| ------ | --------------------------------- | ---------------------------- |
| UC-001 | Registrasi Akun                   | Guest                        |
| UC-002 | Login                             | User (semua role)            |
| UC-003 | Edit Profil                       | User (semua role)            |
| UC-004 | Ganti Password                    | User (semua role)            |
| UC-005 | Logout                            | User (semua role)            |
| UC-006 | Membuat Turnamen                  | Owner                        |
| UC-007 | Edit Konfigurasi Turnamen         | Owner, Co-Organizer          |
| UC-008 | Hapus Turnamen                    | Owner                        |
| UC-009 | Menambah Stage                    | Owner, Co-Organizer          |
| UC-010 | Mempublikasikan Turnamen          | Owner                        |
| UC-011 | Mengelola Pendaftaran Peserta     | Owner, Co-Organizer          |
| UC-012 | Konfirmasi Pembayaran Entry Fee   | Owner, Co-Organizer          |
| UC-013 | Diskualifikasi Peserta            | Owner                        |
| UC-014 | Menambah Staff Turnamen           | Owner                        |
| UC-015 | Set Jadwal Match                  | Owner, Co-Organizer          |
| UC-016 | Memulai Stage                     | Owner, Co-Organizer          |
| UC-017 | Menginput Hasil Match             | Owner, Co-Organizer, Referee |
| UC-018 | Koreksi Hasil Match               | Owner                        |
| UC-019 | Advance ke Stage Berikutnya       | Owner, Co-Organizer          |
| UC-020 | Mendaftar Turnamen (Individual)   | User                         |
| UC-021 | Membuat Tim                       | User                         |
| UC-022 | Mendaftar Turnamen (Tim)          | Captain                      |
| UC-023 | Undang Member ke Tim              | Captain                      |
| UC-024 | Remove Member dari Tim            | Captain                      |
| UC-025 | Keluar dari Tim (Member)          | Member                       |
| UC-026 | Transfer Kepemilikan Tim          | Captain                      |
| UC-027 | Lihat Dashboard Peserta           | User (sebagai Peserta)       |
| UC-028 | Menerima & Membaca Notifikasi     | User (semua role)            |
| UC-029 | Melihat Bracket & Standing Publik | Public Viewer (tanpa login)  |
| UC-030 | Lihat Arsip Turnamen              | Public Viewer (tanpa login)  |
| UC-031 | Review & Approve Turnamen         | Admin Platform               |
| UC-032 | Kelola User (Suspend/Unsuspend)   | Admin Platform               |
| UC-033 | Kelola Daftar Kata Terlarang      | Admin Platform               |
| UC-034 | Kelola Subscription User          | Admin Platform               |
| UC-035 | Lihat Dashboard Platform          | Admin Platform               |
| UC-036 | Promote User menjadi Admin        | Admin Platform               |

---

## 2. Detail Use Case

### 2.1 Manajemen Akun

*Guest / User — Registrasi, login, edit profil, dan logout*

---

#### UC-001 — Registrasi Akun

| Field             | Detail                                                                                     |
| ----------------- | ------------------------------------------------------------------------------------------ |
| **ID Use Case**   | UC-001                                                                                     |
| **Nama**          | Registrasi Akun                                                                            |
| **Aktor**         | Guest                                                                                      |
| **Deskripsi**     | Pengguna baru mendaftar akun ke platform Juara League menggunakan email atau Google OAuth. |
| **Precondition**  | Pengguna belum memiliki akun di platform.                                                  |
| **Postcondition** | Akun aktif dan siap digunakan.                                                             |

**Main Flow:**
1. Pengguna membuka halaman registrasi.
2. Pengguna memilih metode: Email atau Google OAuth.
3. Sistem memvalidasi data.
4. Sistem mengirimkan email verifikasi.
5. Pengguna memverifikasi email.
6. Akun berhasil dibuat dan diarahkan ke dashboard.

**Alternative Flow:**
- 2a. Jika email sudah terdaftar → sistem menyarankan login.
- 5a. Jika link expired → pengguna dapat meminta ulang verifikasi.

---

#### UC-002 — Login

| Field             | Detail                                               |
| ----------------- | ---------------------------------------------------- |
| **ID Use Case**   | UC-002                                               |
| **Nama**          | Login                                                |
| **Aktor**         | User (semua role)                                    |
| **Deskripsi**     | Pengguna yang sudah memiliki akun masuk ke platform. |
| **Precondition**  | Pengguna sudah memiliki akun terdaftar.              |
| **Postcondition** | Pengguna berhasil login dan session aktif.           |

**Main Flow:**
1. Pengguna membuka halaman login.
2. Pengguna memasukkan email & password, atau login via Google.
3. Sistem memvalidasi kredensial.
4. Pengguna diarahkan ke dashboard.

**Alternative Flow:**
- 3a. Jika kredensial salah → tampilkan pesan error.
- 3b. Jika akun belum diverifikasi → sistem meminta verifikasi email.

---

#### UC-003 — Edit Profil

| Field             | Detail                                                       |
| ----------------- | ------------------------------------------------------------ |
| **ID Use Case**   | UC-003                                                       |
| **Nama**          | Edit Profil                                                  |
| **Aktor**         | User (semua role)                                            |
| **Deskripsi**     | Pengguna memperbarui informasi profil seperti nama dan foto. |
| **Precondition**  | Pengguna sudah login.                                        |
| **Postcondition** | Data profil pengguna berhasil diperbarui.                    |

**Main Flow:**
1. User membuka halaman pengaturan akun.
2. User memperbarui nama atau foto profil.
3. Sistem memvalidasi dan menyimpan perubahan.

**Alternative Flow:** —

---

#### UC-004 — Ganti Password

| Field             | Detail                                    |
| ----------------- | ----------------------------------------- |
| **ID Use Case**   | UC-004                                    |
| **Nama**          | Ganti Password                            |
| **Aktor**         | User (semua role)                         |
| **Deskripsi**     | Pengguna mengganti password akun.         |
| **Precondition**  | Pengguna sudah login dengan metode email. |
| **Postcondition** | Password akun berhasil diperbarui.        |

**Main Flow:**
1. User membuka halaman pengaturan akun.
2. User memasukkan password lama dan password baru.
3. Sistem memvalidasi password lama.
4. Password berhasil diperbarui.

**Alternative Flow:**
- 3a. Jika password lama salah → sistem menampilkan pesan error.

---

#### UC-005 — Logout

| Field             | Detail                                    |
| ----------------- | ----------------------------------------- |
| **ID Use Case**   | UC-005                                    |
| **Nama**          | Logout                                    |
| **Aktor**         | User (semua role)                         |
| **Deskripsi**     | Pengguna keluar dari sesi aktif platform. |
| **Precondition**  | Pengguna sudah login.                     |
| **Postcondition** | Session pengguna berakhir.                |

**Main Flow:**
1. User mengklik tombol logout.
2. Sistem mengakhiri session aktif.
3. User diarahkan ke halaman login.

**Alternative Flow:** —

---

### 2.2 Organizer — Manajemen Turnamen

*Owner / Co-Organizer / Referee — Membuat, mengkonfigurasi, dan menjalankan turnamen*

---

#### UC-006 — Membuat Turnamen

| Field             | Detail                                                               |
| ----------------- | -------------------------------------------------------------------- |
| **ID Use Case**   | UC-006                                                               |
| **Nama**          | Membuat Turnamen                                                     |
| **Aktor**         | Owner                                                                |
| **Deskripsi**     | Organizer membuat turnamen baru dengan mengisi informasi dasar.      |
| **Precondition**  | Organizer sudah login.                                               |
| **Postcondition** | Turnamen berhasil dibuat berstatus Draft dengan URL publik tersedia. |

**Main Flow:**
1. Organizer membuka halaman 'Buat Turnamen'.
2. Organizer mengisi nama, jenis sport, tipe peserta (Individual/Tim).
3. Organizer mengisi deskripsi, banner, entry fee, prize pool (opsional).
4. Organizer mengatur mode registrasi dan batas peserta.
5. Organizer menyimpan turnamen.
6. Sistem membuat halaman publik dengan URL unik.
7. Turnamen berstatus Draft.

**Alternative Flow:**
- 5a. Jika data wajib tidak lengkap → sistem menampilkan validasi error.

---

#### UC-007 — Edit Konfigurasi Turnamen

| Field             | Detail                                                                  |
| ----------------- | ----------------------------------------------------------------------- |
| **ID Use Case**   | UC-007                                                                  |
| **Nama**          | Edit Konfigurasi Turnamen                                               |
| **Aktor**         | Owner, Co-Organizer                                                     |
| **Deskripsi**     | Organizer mengubah informasi atau konfigurasi turnamen sebelum dimulai. |
| **Precondition**  | Turnamen berstatus Draft atau Registration.                             |
| **Postcondition** | Konfigurasi turnamen berhasil diperbarui.                               |

**Main Flow:**
1. Organizer membuka halaman manajemen turnamen.
2. Organizer mengubah informasi yang diinginkan (nama, deskripsi, banner, entry fee, dll).
3. Sistem menyimpan perubahan.

**Alternative Flow:**
- 2a. Jika turnamen sudah berstatus Ongoing → beberapa field tidak dapat diubah.

---

#### UC-008 — Hapus Turnamen

| Field             | Detail                                                               |
| ----------------- | -------------------------------------------------------------------- |
| **ID Use Case**   | UC-008                                                               |
| **Nama**          | Hapus Turnamen                                                       |
| **Aktor**         | Owner                                                                |
| **Deskripsi**     | Owner menghapus turnamen yang belum dimulai.                         |
| **Precondition**  | Turnamen berstatus Draft atau Registration. Owner adalah pembuatnya. |
| **Postcondition** | Turnamen berhasil dihapus dari sistem.                               |

**Main Flow:**
1. Owner membuka halaman manajemen turnamen.
2. Owner memilih 'Hapus Turnamen'.
3. Sistem menampilkan konfirmasi.
4. Owner mengkonfirmasi penghapusan.
5. Turnamen dan semua data terkait dihapus.

**Alternative Flow:**
- 3a. Jika turnamen sudah Ongoing → sistem menolak penghapusan.

---

#### UC-009 — Menambah Stage

| Field             | Detail                                                                       |
| ----------------- | ---------------------------------------------------------------------------- |
| **ID Use Case**   | UC-009                                                                       |
| **Nama**          | Menambah Stage                                                               |
| **Aktor**         | Owner, Co-Organizer                                                          |
| **Deskripsi**     | Organizer menambahkan stage ke dalam turnamen beserta konfigurasi formatnya. |
| **Precondition**  | Turnamen berstatus Draft atau Registration.                                  |
| **Postcondition** | Stage berhasil ditambahkan dengan konfigurasi yang dipilih.                  |

**Main Flow:**
1. Organizer membuka halaman konfigurasi turnamen.
2. Organizer menambahkan stage baru.
3. Organizer mengisi nama stage dan memilih format.
4. Organizer mengatur BO setting dan jumlah peserta yang lolos.
5. Khusus Round Robin: atur jumlah grup dan peserta per grup.
6. Sistem menampilkan rekomendasi atau peringatan jika konfigurasi kurang ideal.
7. Stage berhasil disimpan.

**Alternative Flow:**
- 6a. Round Robin > 32 peserta → sistem menampilkan warning.

---

#### UC-010 — Mempublikasikan Turnamen

| Field             | Detail                                                               |
| ----------------- | -------------------------------------------------------------------- |
| **ID Use Case**   | UC-010                                                               |
| **Nama**          | Mempublikasikan Turnamen                                             |
| **Aktor**         | Owner                                                                |
| **Deskripsi**     | Organizer mengubah status turnamen dari Draft ke Registration.       |
| **Precondition**  | Turnamen berstatus Draft dan minimal satu stage sudah dikonfigurasi. |
| **Postcondition** | Turnamen berstatus Registration dan terbuka untuk pendaftaran.       |

**Main Flow:**
1. Organizer mengklik 'Publikasikan Turnamen'.
2. Sistem memvalidasi kelengkapan konfigurasi.
3. Status turnamen berubah menjadi Registration.
4. Turnamen muncul di halaman publik dan terbuka untuk pendaftaran.

**Alternative Flow:**
- 2a. Belum ada stage → sistem meminta organizer menambah stage dulu.

---

#### UC-011 — Mengelola Pendaftaran Peserta

| Field             | Detail                                                                 |
| ----------------- | ---------------------------------------------------------------------- |
| **ID Use Case**   | UC-011                                                                 |
| **Nama**          | Mengelola Pendaftaran Peserta                                          |
| **Aktor**         | Owner, Co-Organizer                                                    |
| **Deskripsi**     | Organizer menyetujui, menolak, atau menambahkan peserta secara manual. |
| **Precondition**  | Turnamen berstatus Registration.                                       |
| **Postcondition** | Daftar peserta turnamen ter-update.                                    |

**Main Flow:**
1. Organizer membuka halaman daftar pendaftar.
2. Mode Open: organizer menyetujui atau menolak setiap pendaftaran.
3. Mode Invite-only: organizer mencari akun user dan menambahkan langsung.
4. Sistem mengirimkan notifikasi ke peserta.

**Alternative Flow:**
- 3a. Jika user tidak ditemukan → sistem menampilkan pesan error.

---

#### UC-012 — Konfirmasi Pembayaran Entry Fee

| Field             | Detail                                                               |
| ----------------- | -------------------------------------------------------------------- |
| **ID Use Case**   | UC-012                                                               |
| **Nama**          | Konfirmasi Pembayaran Entry Fee                                      |
| **Aktor**         | Owner, Co-Organizer                                                  |
| **Deskripsi**     | Organizer mengkonfirmasi pembayaran entry fee peserta secara manual. |
| **Precondition**  | Turnamen berbayar (entry fee > 0). Peserta sudah mendaftar.          |
| **Postcondition** | Status pembayaran peserta berhasil diperbarui menjadi Paid.          |

**Main Flow:**
1. Organizer membuka halaman daftar pendaftar.
2. Organizer melihat peserta dengan status pembayaran Pending.
3. Organizer memverifikasi bukti pembayaran (di luar platform).
4. Organizer mengkonfirmasi pembayaran di platform.
5. Status pembayaran peserta berubah menjadi Paid.
6. Sistem mengirim notifikasi ke peserta.

**Alternative Flow:**
- 4a. Jika bukti tidak valid → organizer menolak pendaftaran.

---

#### UC-013 — Diskualifikasi Peserta

| Field             | Detail                                                              |
| ----------------- | ------------------------------------------------------------------- |
| **ID Use Case**   | UC-013                                                              |
| **Nama**          | Diskualifikasi Peserta                                              |
| **Aktor**         | Owner                                                               |
| **Deskripsi**     | Owner mendiskualifikasi peserta dari turnamen yang sedang berjalan. |
| **Precondition**  | Turnamen berstatus Ongoing. Peserta terdaftar aktif.                |
| **Postcondition** | Peserta berhasil didiskualifikasi dan dihapus dari kompetisi aktif. |

**Main Flow:**
1. Owner membuka halaman manajemen peserta.
2. Owner memilih peserta yang akan didiskualifikasi.
3. Owner mengisi alasan diskualifikasi.
4. Sistem mengkonfirmasi tindakan.
5. Peserta dihapus dari bracket/standing.
6. Sistem mengirim notifikasi ke peserta terkait.

**Alternative Flow:** —

---

#### UC-014 — Menambah Staff Turnamen

| Field             | Detail                                                            |
| ----------------- | ----------------------------------------------------------------- |
| **ID Use Case**   | UC-014                                                            |
| **Nama**          | Menambah Staff Turnamen                                           |
| **Aktor**         | Owner                                                             |
| **Deskripsi**     | Owner mengundang pengguna lain menjadi Co-Organizer atau Referee. |
| **Precondition**  | Turnamen sudah dibuat. Calon staff sudah memiliki akun.           |
| **Postcondition** | Staff berhasil ditambahkan dengan role yang sesuai.               |

**Main Flow:**
1. Owner membuka halaman manajemen staff.
2. Owner mencari akun pengguna berdasarkan email.
3. Owner memilih role: Co-Organizer atau Referee.
4. Sistem mengirimkan undangan.
5. Pengguna menerima undangan.
6. Staff berhasil ditambahkan.

**Alternative Flow:**
- 5a. Jika ditolak → pengguna tidak ditambahkan sebagai staff.

---

#### UC-015 — Set Jadwal Match

| Field             | Detail                                                               |
| ----------------- | -------------------------------------------------------------------- |
| **ID Use Case**   | UC-015                                                               |
| **Nama**          | Set Jadwal Match                                                     |
| **Aktor**         | Owner, Co-Organizer                                                  |
| **Deskripsi**     | Organizer menetapkan waktu pelaksanaan untuk match tertentu.         |
| **Precondition**  | Stage berstatus Ongoing. Match berstatus Upcoming.                   |
| **Postcondition** | Jadwal match berhasil ditetapkan dan notifikasi terkirim ke peserta. |

**Main Flow:**
1. Organizer membuka halaman detail match.
2. Organizer mengisi tanggal dan waktu match.
3. Sistem menyimpan jadwal.
4. Sistem mengirimkan notifikasi ke peserta terkait.

**Alternative Flow:** —

---

#### UC-016 — Memulai Stage

| Field             | Detail                                                                             |
| ----------------- | ---------------------------------------------------------------------------------- |
| **ID Use Case**   | UC-016                                                                             |
| **Nama**          | Memulai Stage                                                                      |
| **Aktor**         | Owner, Co-Organizer                                                                |
| **Deskripsi**     | Organizer memulai stage sehingga sistem otomatis men-generate bracket atau jadwal. |
| **Precondition**  | Turnamen berstatus Registration. Minimal 2 peserta terdaftar dan approved.         |
| **Postcondition** | Stage aktif dan semua match berhasil di-generate.                                  |

**Main Flow:**
1. Organizer mengklik 'Mulai Stage'.
2. Sistem mengacak urutan peserta (jika tidak ada seeding).
3. Sistem men-generate bracket/jadwal berdasarkan format yang dipilih.
4. Status turnamen berubah menjadi Ongoing.
5. Semua peserta mendapat notifikasi jadwal match.

**Alternative Flow:**
- 3a. Round Robin → sistem membagi peserta ke grup.
- 3b. Swiss → sistem menetapkan jumlah ronde berdasarkan peserta.

---

#### UC-017 — Menginput Hasil Match

| Field             | Detail                                                            |
| ----------------- | ----------------------------------------------------------------- |
| **ID Use Case**   | UC-017                                                            |
| **Nama**          | Menginput Hasil Match                                             |
| **Aktor**         | Owner, Co-Organizer, Referee                                      |
| **Deskripsi**     | Staff menginput hasil pertandingan setelah match selesai.         |
| **Precondition**  | Stage berstatus Ongoing. Match berstatus Upcoming atau Ongoing.   |
| **Postcondition** | Hasil tersimpan, bracket/standing ter-update, pemenang diadvance. |

**Main Flow:**
1. Staff membuka halaman detail match.
2. Staff mengubah status match menjadi Ongoing.
3. Staff menginput skor per game (jika BO > 1).
4. Sistem menentukan pemenang berdasarkan hasil game.
5. Status match berubah menjadi Completed.
6. Sistem memperbarui bracket/standing otomatis.
7. Pemenang diadvance ke ronde berikutnya.

**Alternative Flow:**
- 4a. Jika belum ada pemenang (BO3 masih 1-1) → lanjutkan input game berikutnya.

---

#### UC-018 — Koreksi Hasil Match

| Field             | Detail                                                                |
| ----------------- | --------------------------------------------------------------------- |
| **ID Use Case**   | UC-018                                                                |
| **Nama**          | Koreksi Hasil Match                                                   |
| **Aktor**         | Owner                                                                 |
| **Deskripsi**     | Owner mengubah hasil match yang sudah diinput jika terjadi kesalahan. |
| **Precondition**  | Match berstatus Completed. Hanya Owner yang dapat melakukan ini.      |
| **Postcondition** | Hasil match berhasil dikoreksi dan bracket/standing disesuaikan.      |

**Main Flow:**
1. Owner membuka halaman detail match.
2. Owner memilih 'Edit Hasil'.
3. Owner mengubah skor atau pemenang.
4. Sistem memperbarui bracket/standing secara otomatis.
5. Sistem mengirim notifikasi ke peserta terkait.

**Alternative Flow:** —

---

#### UC-019 — Advance ke Stage Berikutnya

| Field             | Detail                                                                     |
| ----------------- | -------------------------------------------------------------------------- |
| **ID Use Case**   | UC-019                                                                     |
| **Nama**          | Advance ke Stage Berikutnya                                                |
| **Aktor**         | Owner, Co-Organizer                                                        |
| **Deskripsi**     | Organizer memindahkan peserta yang lolos ke stage berikutnya.              |
| **Precondition**  | Semua match di stage saat ini berstatus Completed.                         |
| **Postcondition** | Peserta lolos terdaftar di stage berikutnya dan bracket baru ter-generate. |

**Main Flow:**
1. Sistem menandai stage saat ini sebagai Completed.
2. Sistem menentukan peserta yang lolos berdasarkan konfigurasi (top N).
3. Organizer mengkonfirmasi daftar peserta yang lolos.
4. Organizer memulai stage berikutnya.
5. Sistem men-generate bracket/jadwal stage baru.

**Alternative Flow:**
- 3a. Jika ada tie di posisi cutoff → organizer menentukan secara manual.

---

### 2.3 Peserta — Tim & Pendaftaran

*User / Captain / Member — Mendaftar turnamen dan mengelola tim*

---

#### UC-020 — Mendaftar Turnamen (Individual)

| Field             | Detail                                                                                          |
| ----------------- | ----------------------------------------------------------------------------------------------- |
| **ID Use Case**   | UC-020                                                                                          |
| **Nama**          | Mendaftar Turnamen (Individual)                                                                 |
| **Aktor**         | User                                                                                            |
| **Deskripsi**     | Pengguna mendaftarkan diri ke turnamen bertipe Individual.                                      |
| **Precondition**  | User sudah login. Turnamen berstatus Registration. Tipe peserta: Individual. Kuota belum penuh. |
| **Postcondition** | Pendaftaran tercatat dengan status Pending atau Approved.                                       |

**Main Flow:**
1. User membuka halaman publik turnamen.
2. User mengklik 'Daftar Sekarang'.
3. Turnamen berbayar: user melihat info entry fee dan cara pembayaran.
4. User mengkonfirmasi pendaftaran.
5. Sistem mengirim notifikasi ke organizer.
6. User menunggu approval (Open) atau langsung masuk (Invite).

**Alternative Flow:**
- 3a. Turnamen gratis → langsung ke step 4.
- 6a. Jika ditolak → user mendapat notifikasi penolakan.

---

#### UC-021 — Membuat Tim

| Field             | Detail                                                                     |
| ----------------- | -------------------------------------------------------------------------- |
| **ID Use Case**   | UC-021                                                                     |
| **Nama**          | Membuat Tim                                                                |
| **Aktor**         | User                                                                       |
| **Deskripsi**     | Pengguna membuat tim baru untuk diikutsertakan dalam turnamen bertipe Tim. |
| **Precondition**  | User sudah login.                                                          |
| **Postcondition** | Tim berhasil dibuat dengan user sebagai Captain, status Active.            |

**Main Flow:**
1. User membuka halaman 'Buat Tim'.
2. User mengisi nama tim dan mengupload logo (opsional).
3. Sistem membuat tim dengan user sebagai Captain.
4. User dapat langsung mengundang member (opsional).

**Alternative Flow:** —

---

#### UC-022 — Mendaftar Turnamen (Tim)

| Field             | Detail                                                                                      |
| ----------------- | ------------------------------------------------------------------------------------------- |
| **ID Use Case**   | UC-022                                                                                      |
| **Nama**          | Mendaftar Turnamen (Tim)                                                                    |
| **Aktor**         | Captain                                                                                     |
| **Deskripsi**     | Captain mendaftarkan timnya ke turnamen bertipe Tim.                                        |
| **Precondition**  | User adalah Captain. Turnamen berstatus Registration. Tipe peserta: Tim. Kuota belum penuh. |
| **Postcondition** | Pendaftaran tim tercatat dengan status Pending atau Approved.                               |

**Main Flow:**
1. Captain membuka halaman publik turnamen.
2. Captain mengklik 'Daftar Tim'.
3. Captain memilih tim yang akan didaftarkan.
4. Turnamen berbayar: Captain melihat info entry fee.
5. Captain mengkonfirmasi pendaftaran.
6. Sistem mengirim notifikasi ke organizer.
7. Tim menunggu approval.

**Alternative Flow:**
- 3a. Captain tidak punya tim → sistem menawarkan buat tim baru.

---

#### UC-023 — Undang Member ke Tim

| Field             | Detail                                                          |
| ----------------- | --------------------------------------------------------------- |
| **ID Use Case**   | UC-023                                                          |
| **Nama**          | Undang Member ke Tim                                            |
| **Aktor**         | Captain                                                         |
| **Deskripsi**     | Captain mengundang pengguna lain untuk bergabung ke roster tim. |
| **Precondition**  | User adalah Captain. Calon member sudah memiliki akun.          |
| **Postcondition** | Member berhasil bergabung ke roster tim.                        |

**Main Flow:**
1. Captain membuka halaman manajemen tim.
2. Captain mencari akun pengguna berdasarkan email.
3. Captain mengirim undangan.
4. Calon member menerima undangan.
5. Member berhasil ditambahkan ke roster.

**Alternative Flow:**
- 4a. Jika ditolak → member tidak ditambahkan.

---

#### UC-024 — Remove Member dari Tim

| Field             | Detail                                                  |
| ----------------- | ------------------------------------------------------- |
| **ID Use Case**   | UC-024                                                  |
| **Nama**          | Remove Member dari Tim                                  |
| **Aktor**         | Captain                                                 |
| **Deskripsi**     | Captain mengeluarkan member dari roster tim.            |
| **Precondition**  | User adalah Captain. Member terdaftar dalam roster tim. |
| **Postcondition** | Member berhasil dikeluarkan dari roster tim.            |

**Main Flow:**
1. Captain membuka halaman manajemen tim.
2. Captain memilih member yang akan dikeluarkan.
3. Sistem meminta konfirmasi.
4. Captain mengkonfirmasi.
5. Member dihapus dari roster.

**Alternative Flow:** —

---

#### UC-025 — Keluar dari Tim (Member)

| Field             | Detail                                              |
| ----------------- | --------------------------------------------------- |
| **ID Use Case**   | UC-025                                              |
| **Nama**          | Keluar dari Tim (Member)                            |
| **Aktor**         | Member                                              |
| **Deskripsi**     | Member keluar dari tim yang diikutinya.             |
| **Precondition**  | User adalah Member (bukan Captain) dari sebuah tim. |
| **Postcondition** | Member berhasil keluar dari tim.                    |

**Main Flow:**
1. Member membuka halaman detail tim.
2. Member memilih 'Keluar dari Tim'.
3. Sistem meminta konfirmasi.
4. Member mengkonfirmasi.
5. Member dihapus dari roster.

**Alternative Flow:** —

---

#### UC-026 — Transfer Kepemilikan Tim

| Field             | Detail                                                   |
| ----------------- | -------------------------------------------------------- |
| **ID Use Case**   | UC-026                                                   |
| **Nama**          | Transfer Kepemilikan Tim                                 |
| **Aktor**         | Captain                                                  |
| **Deskripsi**     | Captain memindahkan status Captain ke member lain.       |
| **Precondition**  | User adalah Captain. Tim memiliki minimal 1 member lain. |
| **Postcondition** | Kepemilikan tim berhasil dipindahkan.                    |

**Main Flow:**
1. Captain membuka halaman manajemen tim.
2. Captain memilih member yang akan dijadikan Captain baru.
3. Captain mengkonfirmasi transfer.
4. Role diperbarui: Captain lama jadi Member, member terpilih jadi Captain.

**Alternative Flow:** —

---

### 2.4 Peserta — Dashboard & Notifikasi

---

#### UC-027 — Lihat Dashboard Peserta

| Field             | Detail                                                                              |
| ----------------- | ----------------------------------------------------------------------------------- |
| **ID Use Case**   | UC-027                                                                              |
| **Nama**          | Lihat Dashboard Peserta                                                             |
| **Aktor**         | User (sebagai Peserta)                                                              |
| **Deskripsi**     | Peserta melihat turnamen yang diikuti, jadwal match, dan hasil pertandingan mereka. |
| **Precondition**  | User sudah login dan terdaftar minimal di satu turnamen.                            |
| **Postcondition** | Peserta mendapatkan informasi terkini tentang partisipasi mereka.                   |

**Main Flow:**
1. User membuka dashboard peserta.
2. Sistem menampilkan daftar turnamen yang diikuti.
3. User dapat melihat jadwal match berikutnya.
4. User dapat melihat hasil match yang sudah selesai.
5. User dapat melihat posisi di standing (jika Round Robin/Swiss).

**Alternative Flow:** —

---

#### UC-028 — Menerima & Membaca Notifikasi

| Field             | Detail                                                                                               |
| ----------------- | ---------------------------------------------------------------------------------------------------- |
| **ID Use Case**   | UC-028                                                                                               |
| **Nama**          | Menerima & Membaca Notifikasi                                                                        |
| **Aktor**         | User (semua role)                                                                                    |
| **Deskripsi**     | User menerima dan membaca notifikasi dari aktivitas yang berkaitan dengan akun atau turnamen mereka. |
| **Precondition**  | User sudah login.                                                                                    |
| **Postcondition** | Notifikasi ditandai sebagai sudah dibaca.                                                            |

**Main Flow:**
1. Sistem mengirim notifikasi ke user berdasarkan event yang relevan.
2. User membuka panel notifikasi.
3. Sistem menampilkan daftar notifikasi terbaru.
4. User mengklik notifikasi untuk melihat detail atau diarahkan ke halaman terkait.
5. Notifikasi ditandai sudah dibaca.

**Alternative Flow:** —

---

### 2.5 Publik — Viewer

---

#### UC-029 — Melihat Bracket & Standing Publik

| Field             | Detail                                                                                     |
| ----------------- | ------------------------------------------------------------------------------------------ |
| **ID Use Case**   | UC-029                                                                                     |
| **Nama**          | Melihat Bracket & Standing Publik                                                          |
| **Aktor**         | Public Viewer (tanpa login)                                                                |
| **Deskripsi**     | Siapa saja dapat melihat bracket dan standing turnamen secara real-time tanpa harus login. |
| **Precondition**  | Turnamen berstatus Ongoing atau Completed.                                                 |
| **Postcondition** | Viewer mendapatkan informasi terkini tanpa perlu login.                                    |

**Main Flow:**
1. Viewer membuka URL publik turnamen.
2. Sistem menampilkan informasi turnamen: nama, sport, format, peserta.
3. Viewer dapat melihat bracket/standing real-time.
4. Viewer dapat melihat jadwal dan hasil match.
5. Viewer dapat membagikan URL.

**Alternative Flow:** —

---

#### UC-030 — Lihat Arsip Turnamen

| Field             | Detail                                                                          |
| ----------------- | ------------------------------------------------------------------------------- |
| **ID Use Case**   | UC-030                                                                          |
| **Nama**          | Lihat Arsip Turnamen                                                            |
| **Aktor**         | Public Viewer (tanpa login)                                                     |
| **Deskripsi**     | Siapa saja dapat melihat hasil akhir turnamen yang sudah selesai sebagai arsip. |
| **Precondition**  | Turnamen berstatus Completed.                                                   |
| **Postcondition** | Viewer mendapatkan data arsip turnamen yang sudah selesai.                      |

**Main Flow:**
1. Viewer membuka URL publik turnamen.
2. Sistem menampilkan hasil akhir: juara 1, 2, 3, semua hasil match.
3. Viewer dapat melihat statistik turnamen (total match, peserta, dll).

**Alternative Flow:** —

---

### 2.6 Admin Platform

*Admin Platform — Mengelola platform dari /admin — approval, user, subscription, monitoring*

---

#### UC-031 — Review & Approve Turnamen

| Field             | Detail                                                                                                |
| ----------------- | ----------------------------------------------------------------------------------------------------- |
| **ID Use Case**   | UC-031                                                                                                |
| **Nama**          | Review & Approve Turnamen                                                                             |
| **Aktor**         | Admin Platform                                                                                        |
| **Deskripsi**     | Admin mereview turnamen yang masuk antrian `pending_review` dan memutuskan untuk approve atau reject. |
| **Precondition**  | Admin sudah login. Ada turnamen dengan `approval_status = pending_review`.                            |
| **Postcondition** | Turnamen berstatus draft (approved) atau tetap pending dengan catatan rejection.                      |

**Main Flow:**
1. Admin membuka halaman antrian review di `/admin`.
2. Admin memilih turnamen yang akan direview.
3. Sistem menampilkan detail turnamen, profil organizer, dan log hasil auto-check.
4. Admin memeriksa kelayakan turnamen.
5. Admin memilih Approve atau Reject.
6. Jika Reject, admin mengisi alasan penolakan (wajib).
7. Sistem mengupdate `approval_status` dan mengirim notifikasi ke organizer.

**Alternative Flow:**
- 5a. Jika Approve: turnamen berstatus draft, organizer dapat melanjutkan setup.
- 5b. Jika Reject: organizer mendapat notifikasi beserta alasan.

---

#### UC-032 — Kelola User (Suspend/Unsuspend)

| Field             | Detail                                                                                                                             |
| ----------------- | ---------------------------------------------------------------------------------------------------------------------------------- |
| **ID Use Case**   | UC-032                                                                                                                             |
| **Nama**          | Kelola User (Suspend/Unsuspend)                                                                                                    |
| **Aktor**         | Admin Platform                                                                                                                     |
| **Deskripsi**     | Admin dapat menonaktifkan (suspend) akun user yang melanggar aturan platform, atau mengaktifkan kembali akun yang sudah disuspend. |
| **Precondition**  | Admin sudah login. User yang ditarget terdaftar di sistem.                                                                         |
| **Postcondition** | Status akun user berhasil diubah. User tidak dapat login selama suspend.                                                           |

**Main Flow:**
1. Admin membuka halaman manajemen user di `/admin`.
2. Admin mencari user berdasarkan nama atau email.
3. Admin membuka detail profil user.
4. Admin memilih Suspend atau Unsuspend.
5. Jika Suspend, admin mengisi alasan dan durasi suspend (atau permanent).
6. Sistem mengupdate status user dan mengirim notifikasi ke user.

**Alternative Flow:**
- 5a. Suspend sementara: akun aktif kembali otomatis setelah durasi habis.
- 5b. Suspend permanent: harus di-unsuspend manual oleh admin.

---

#### UC-033 — Kelola Daftar Kata Terlarang

| Field             | Detail                                                                                                    |
| ----------------- | --------------------------------------------------------------------------------------------------------- |
| **ID Use Case**   | UC-033                                                                                                    |
| **Nama**          | Kelola Daftar Kata Terlarang                                                                              |
| **Aktor**         | Admin Platform                                                                                            |
| **Deskripsi**     | Admin mengelola daftar kata yang tidak boleh muncul di nama turnamen untuk keperluan auto-approve filter. |
| **Precondition**  | Admin sudah login.                                                                                        |
| **Postcondition** | Daftar kata terlarang ter-update dan berlaku untuk proses auto-approve berikutnya.                        |

**Main Flow:**
1. Admin membuka halaman konfigurasi kata terlarang di `/admin`.
2. Admin melihat daftar kata terlarang yang aktif.
3. Admin dapat menambah kata baru, mengedit, atau menghapus kata yang sudah ada.
4. Sistem menyimpan perubahan dan langsung berlaku untuk turnamen yang dibuat berikutnya.

**Alternative Flow:** —

---

#### UC-034 — Kelola Subscription User

| Field             | Detail                                                                                                                                |
| ----------------- | ------------------------------------------------------------------------------------------------------------------------------------- |
| **ID Use Case**   | UC-034                                                                                                                                |
| **Nama**          | Kelola Subscription User                                                                                                              |
| **Aktor**         | Admin Platform                                                                                                                        |
| **Deskripsi**     | Admin dapat melihat, mengupgrade, menurunkan, atau membatalkan subscription user secara manual untuk keperluan support atau override. |
| **Precondition**  | Admin sudah login.                                                                                                                    |
| **Postcondition** | Status subscription user berhasil diperbarui.                                                                                         |

**Main Flow:**
1. Admin membuka detail profil user di `/admin`.
2. Admin melihat status subscription aktif user.
3. Admin dapat mengubah plan (free/pro), memperpanjang `expires_at`, atau membatalkan subscription.
4. Sistem mencatat perubahan beserta alasan yang diisi admin.
5. Sistem mengirim notifikasi ke user jika ada perubahan.

**Alternative Flow:** —

---

#### UC-035 — Lihat Dashboard Platform

| Field             | Detail                                                                                             |
| ----------------- | -------------------------------------------------------------------------------------------------- |
| **ID Use Case**   | UC-035                                                                                             |
| **Nama**          | Lihat Dashboard Platform                                                                           |
| **Aktor**         | Admin Platform                                                                                     |
| **Deskripsi**     | Admin melihat overview statistik platform secara keseluruhan untuk monitoring dan decision making. |
| **Precondition**  | Admin sudah login.                                                                                 |
| **Postcondition** | Admin mendapatkan gambaran kondisi platform terkini.                                               |

**Main Flow:**
1. Admin membuka `/admin/dashboard`.
2. Sistem menampilkan statistik: total user terdaftar, total turnamen aktif, turnamen pending review, total sport tersedia, user Pro aktif.
3. Admin dapat melihat grafik pertumbuhan user dan turnamen per periode.
4. Admin dapat melihat daftar turnamen terbaru dan user terbaru.

**Alternative Flow:** —

---

#### UC-036 — Promote User menjadi Admin

| Field             | Detail                                                               |
| ----------------- | -------------------------------------------------------------------- |
| **ID Use Case**   | UC-036                                                               |
| **Nama**          | Promote User menjadi Admin                                           |
| **Aktor**         | Admin Platform                                                       |
| **Deskripsi**     | Admin dapat memberikan atau mencabut hak akses admin dari user lain. |
| **Precondition**  | Admin sudah login. User target terdaftar di sistem.                  |
| **Postcondition** | Status `is_admin` user target berhasil diubah.                       |

**Main Flow:**
1. Admin membuka detail profil user di `/admin`.
2. Admin memilih Toggle Admin Access.
3. Sistem meminta konfirmasi.
4. Admin mengkonfirmasi.
5. Sistem mengupdate `is_admin` user target.

**Alternative Flow:**
- 4a. Jika admin mencabut hak admin dari diri sendiri, sistem menolak jika dia adalah satu-satunya admin.

---

## 3. Relasi Antar Use Case

| Use Case                    | Relasi        | Dengan Use Case           | Keterangan                                                     |
| --------------------------- | ------------- | ------------------------- | -------------------------------------------------------------- |
| UC-006 Membuat Turnamen     | `<<include>>` | UC-009 Menambah Stage     | Turnamen harus memiliki stage sebelum dipublikasikan           |
| UC-010 Mempublikasikan      | `<<include>>` | UC-006 Membuat Turnamen   | Turnamen harus dibuat sebelum dipublikasikan                   |
| UC-016 Memulai Stage        | `<<include>>` | UC-011 Kelola Pendaftaran | Peserta harus terdaftar sebelum stage dimulai                  |
| UC-017 Input Hasil Match    | `<<extend>>`  | UC-019 Advance Stage      | Setelah semua match selesai, bisa advance stage                |
| UC-022 Daftar Tim           | `<<include>>` | UC-021 Membuat Tim        | Captain harus punya tim sebelum daftar turnamen                |
| UC-001 Registrasi           | `<<extend>>`  | UC-002 Login              | Setelah registrasi, user diarahkan login                       |
| UC-017 Input Hasil Match    | `<<extend>>`  | UC-018 Koreksi Hasil      | Owner dapat mengoreksi hasil yang sudah diinput                |
| UC-006 Membuat Turnamen     | `<<extend>>`  | UC-007 Edit Konfigurasi   | Organizer dapat mengedit sebelum turnamen dimulai              |
| UC-006 Membuat Turnamen     | `<<extend>>`  | UC-031 Review Turnamen    | Free user membuat turnamen memicu proses review admin          |
| UC-032 Suspend User         | `<<extend>>`  | UC-031 Review Turnamen    | Admin dapat suspend organizer yang membuat turnamen bermasalah |
| UC-034 Kelola Subscription  | `<<include>>` | UC-032 Kelola User        | Subscription dikelola dalam konteks profil user                |
| UC-029 Lihat Bracket Publik | `<<extend>>`  | UC-030 Lihat Arsip        | Halaman yang sama digunakan untuk arsip setelah selesai        |

---

*— End of Document —*