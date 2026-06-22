# GuruHub - Portal Administrasi & Pembinaan Guru (SaaS Monolith)

GuruHub adalah portal administrasi sekolah terintegrasi yang dirancang untuk membantu guru mengelola dokumen kurikulum harian secara cepat, dinamis, dan terstruktur. Aplikasi ini dibangun ulang menggunakan arsitektur monolitik yang ringan, berkinerja tinggi, dan ramah terhadap *shared web hosting*.

---

## 🛠️ Stack Teknologi

* **Framework Utama**: Laravel 12 (PHP 8.2+)
* **Interaktivitas Frontend**: Livewire v4 (Single-File Components)
* **Styling & UI**: Tailwind CSS v4 (Desain Premium & Responsif Mobile)
* **Pustaka Ikon**: Lucide Icons (Loaded via CDN)
* **Pustaka PDF**: Barryvdh Laravel DomPDF (DomPDF v3)
* **Basis Data**: MySQL (Database-Agnostic melalui Eloquent ORM)
* **Manajemen Aset**: Vite & npm

---

## 📂 Fitur Utama Aplikasi

1. **Sistem Autentikasi**: Pembagian hak akses terproteksi antara peran **ADMIN** dan **GURU** berbasis Laravel Session Auth.
2. **Dashboard Statistik**: Visualisasi analitik tingkat penyelesaian dokumen administrasi menggunakan grafik SVG interaktif.
3. **Modul Manajemen (Admin)**: CRUD data Guru (dengan normalisasi nomor HP otomatis ke format internasional `62xxxx`), Murid, Kelas, Mata Pelajaran, Jadwal Pelajaran, dan Profil Sekolah.
4. **Presensi Kelas (Guru)**: Pengisian data kehadiran harian per jadwal mengajar (Hadir, Izin, Sakit, Alpa) dengan lembar presensi adaptif.
5. **Jurnal Harian Mengajar (Guru)**: Pencatatan materi, kegiatan kelas, dan catatan dengan status `DRAFT` atau `COMPLETED` (Final).
6. **Spreadsheet Nilai TP (Guru)**: Grid penilaian siswa per Tujuan Pembelajaran (TP) dengan kalkulasi rata-rata otomatis, auto-save instan, dan ekspor data langsung ke format CSV.
7. **Buku Agenda Guru (Guru)**: Buku harian kegiatan di luar jam mengajar kelas.
8. **Kartu Pembinaan Guru Wali (Guru)**: Modul pencatatan kasus/bimbingan per kategori (Akademik, Disiplin, Kehadiran, Prestasi, Konseling) untuk murid binaan, dilengkapi tombol ekspor riwayat pembinaan ke dokumen PDF dengan nama berkas standar: `[KELAS]_[NAMA_MURID]_PEMBINAAN.pdf`.
9. **Otomatisasi Scheduler (Cron)**: 
   * Pembersihan berkas logs notifikasi lama (>30 hari).
   * Pengiriman notifikasi peringatan harian otomatis bagi guru yang memiliki jurnal berstatus `DRAFT` lebih dari 3 hari.

---

## 💻 Cara Menjalankan Secara Lokal

### Prasyarat
* PHP >= 8.2 (Pastikan ekstensi `pdo_mysql`, `mbstring`, `openssl`, `xml`, dan `gd` aktif)
* Composer
* Node.js & npm
* MySQL / MariaDB Server

### Langkah-Langkah
1. **Clone Proyek & Masuk Direktori**:
   ```bash
   cd GuruHub
   ```
2. **Instalasi Dependensi PHP**:
   ```bash
   composer install
   ```
3. **Penyalinan & Konfigurasi Berkas Lingkungan**:
   ```bash
   copy .env.example .env
   ```
   *Buka berkas `.env` baru Anda dan sesuaikan pengaturan MySQL (`DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`).*
4. **Generate Application Key**:
   ```bash
   php artisan key:generate
   ```
5. **Migrasi Database & Seeding Data Uji**:
   ```bash
   php artisan migrate --seed
   ```
6. **Instalasi Dependensi Frontend & Kompilasi Aset**:
   ```bash
   npm install
   ```
   *Untuk kompilasi produksi:*
   ```bash
   npm run build
   ```
7. **Menjalankan Server Lokal**:
   *Pada Windows (Powershell diblokir), jalankan melalui Command Prompt:*
   ```cmd
   cmd.exe /c npx concurrently -k "php artisan serve" "npm run dev"
   ```
   Akses aplikasi di browser pada alamat: **http://127.0.0.1:8000**
   * **Kredensial Admin**: `admin@guruhub.com` / `admin123`
   * **Kredensial Guru**: `guru@guruhub.com` / `guru123`

---

## 🚀 Panduan Deployment ke Web Hosting (cPanel / RumahWeb)

Untuk petunjuk lengkap mengenai cara melakukan deployment aplikasi GuruHub ke shared web hosting (cPanel / RumahWeb), silakan merujuk ke berkas dokumentasi terpisah:

👉 **[Panduan Deployment Web Hosting (deployment.md)](deployment.md)**

Di dalam panduan tersebut dibahas mengenai pemisahan folder inti untuk keamanan, migrasi database, symlink storage, konfigurasi cron job scheduler, hingga penanganan error composer (`proc_open` disabled) di shared hosting.
