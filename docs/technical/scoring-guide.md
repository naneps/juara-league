# Panduan Sistem Penilaian: Skor vs Poin

Dalam sistem turnamen seperti Juara League, terdapat perbedaan penting antara **Skor (Score)** dan **Poin (Points)**. Penggunaannya juga berbeda-beda tergantung pada format turnamen yang sedang berjalan.

---

## 1. Perbedaan Mendasar

### Skor (Score)
- **Definisi:** Hasil langsung dari suatu pertandingan atau set/game yang sedang berlangsung.
- **Contoh:** 
  - Futsal: Tim A 3 - 1 Tim B (Skor akhirnya adalah 3 gol vs 1 gol).
  - Mobile Legends (Best of 3): Tim A 2 - 0 Tim B (Skor pertandingannya adalah 2 game vs 0 game).
- **Penggunaan:** Digunakan di **semua mode pertandingan** (Single Elimination, Double Elimination, Round Robin, dll) untuk menentukan siapa pemenang dari match tersebut.

### Poin (Points)
- **Definisi:** Nilai akumulasi yang didapatkan oleh peserta (tim/individu) berdasarkan hasil akhir sejumlah pertandingan.
- **Contoh:**
  - Menang = dapat 3 poin.
  - Seri (Draw) = dapat 1 poin.
  - Kalah = dapat 0 poin.
- **Penggunaan:** **Hanya digunakan dalam format penyisihan grup (Group Stage / Round Robin) atau Liga**. Poin menentukan urutan klasemen (Standings).

---

## 2. Penggunaan Berdasarkan Format Turnamen

### A. Format Eliminasi (Single / Double Elimination)
- **Hanya menggunakan SKOR.**
- Pemenang ditentukan dari skor pertandingan. Siapa yang skornya lebih tinggi (atau mencapai target kemenangan misalnya Best of 3), langsung lolos ke ronde berikutnya atau braket selanjutnya (Upper/Lower).
- **Tidak ada sistem POIN**. Tim yang kalah langsung gugur (Single) atau turun ke Lower Bracket (Double).

### B. Format Grup / Round Robin / Liga
- **Menggunakan KEDUANYA (Skor dan Poin).**
- **Skor** digunakan untuk masing-masing match yang dimainkan di dalam grup.
- Hasil dari skor pertandingan tersebut dikonversi menjadi **Poin** klasemen.
- **Tie-breaker:** Jika ada dua tim dengan poin yang sama, *Selisih Skor (Score Difference / Goal Difference)* biasanya digunakan untuk menentukan siapa yang peringkatnya lebih tinggi.

---

## 3. Kesimpulan

- **Skor** adalah *angka hasil pertandingan*. Berlaku di semua mode.
- **Poin** adalah *reward klasemen* akibat menang/seri di fase grup. Tidak dipakai di sistem gugur (elimination bracket).

**Saran Implementasi di Juara League:**
- Di tabel *Matches* atau *Games*, kita cukup menyimpan `score_p1` dan `score_p2`.
- Fitur penghitungan **Poin** hanya diaktifkan/dihitung saat aplikasi me-render klasemen grup (`groups/standings`). Poin ini bisa dikalkulasi secara dinamis saat data pertandingan di-fetch, atau disimpan tersendiri jika performa menjadi isu.
