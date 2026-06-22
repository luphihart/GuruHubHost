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

## 🚀 Panduan Lengkap Deploy di Shared Hosting (RumahWeb / cPanel)

RumahWeb menggunakan panel administrasi **cPanel** dan server berbasis Linux. Demi alasan keamanan terbaik, **JANGAN UPLOAD seluruh file Laravel ke dalam folder `public_html`**. Kita akan memisahkan core Laravel (di luar `public_html`) dengan file publik.

### Langkah 1: Persiapan Berkas di Komputer Lokal
1. Pastikan Anda telah mengompilasi aset CSS & JS untuk produksi di komputer lokal Anda:
   ```bash
   npm run build
   ```
2. Buat arsip `.zip` dari seluruh direktori proyek GuruHub Anda. **Kecualikan** folder berikut agar ukuran berkas tidak membengkak:
   * `node_modules/`
   * `tests/`
   * `old-backend/` dan `old-frontend/` (folder cadangan)
   * `.git/` dan `.github/`
   * `storage/framework/cache/data/*`
   * `storage/logs/*.log`

### Langkah 2: Unggah dan Ekstrak Berkas di cPanel
1. Masuk ke **cPanel RumahWeb** Anda.
2. Buka menu **File Manager**.
3. Pastikan Anda berada di direktori home Anda (misal: `/home/username/`, bukan di dalam `public_html`).
4. Buat folder baru dengan nama `guruhub_app` di luar `public_html` (Path lengkap: `/home/username/guruhub_app`).
5. Unggah berkas `.zip` yang sudah Anda buat ke dalam folder `/home/username/guruhub_app/`, lalu klik kanan dan pilih **Extract**.

### Langkah 3: Pindahkan Berkas Publik ke `public_html`
1. Buka folder `/home/username/guruhub_app/public/`.
2. Pilih seluruh file dan folder di dalamnya (termasuk berkas `.htaccess`, `index.php`, dan folder `build`).
3. Klik tombol **Move** (Pindah) di bagian atas File Manager cPanel, arahkan target tujuan ke **`/public_html`** (atau sub-domain Anda, misal `/public_html/guruhub`).
4. Sekarang, folder `/home/username/guruhub_app/public/` Anda harus kosong, dan seluruh isinya telah berpindah ke `/home/username/public_html/`.

### Langkah 4: Modifikasi Path pada `public_html/index.php`
Karena struktur folder inti telah dipindahkan, kita harus mengarahkan bootstrapper Laravel ke direktori baru.
1. Buka folder `public_html/` Anda.
2. Klik kanan pada berkas **`index.php`** dan pilih **Edit**.
3. Cari baris pemuatan autoloader (biasanya di sekitar baris 30-50) dan ubah path-nya:
   ```diff
   - require __DIR__.'/../vendor/autoload.php';
   + require __DIR__.'/../guruhub_app/vendor/autoload.php';
   ```
4. Cari baris pemuatan bootstrap app (di bagian akhir file) dan ubah path-nya:
   ```diff
   - $app = require_once __DIR__.'/../bootstrap/app.php';
   + $app = require_once __DIR__.'/../guruhub_app/bootstrap/app.php';
   ```
5. Simpan file dan tutup editor.

### Langkah 5: Konfigurasi Database MySQL di RumahWeb
1. Di cPanel RumahWeb, cari dan klik menu **MySQL® Database Wizard**.
2. **Step 1**: Buat database baru (misal: `namauser_guruhubdb`). Klik *Next Step*.
3. **Step 2**: Buat user database baru (misal: `namauser_guruhubuser`) beserta kata sandi yang kuat. Klik *Create User*.
4. **Step 3**: Centang pilihan **ALL PRIVILEGES** untuk memberikan hak akses penuh user terhadap database tersebut. Klik *Next Step*.
5. Catat nama database, user database, dan password yang telah Anda buat.

### Langkah 6: Edit File `.env` di cPanel
1. Buka folder `/home/username/guruhub_app/`.
2. Cari berkas `.env` (Jika tidak terlihat, klik *Settings* di pojok kanan atas File Manager cPanel, centang *Show Hidden Files*, lalu *Save*).
3. Klik kanan pada `.env` dan pilih **Edit**. Sesuaikan konfigurasi berikut:
   ```env
   APP_NAME=GuruHub
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://nama-domain-anda.com

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=namauser_guruhubdb
   DB_USERNAME=namauser_guruhubuser
   DB_PASSWORD=password_yang_anda_buat_tadi
   ```
4. Simpan berkas `.env`.

### Langkah 7: Migrasi Database Ke Live Hosting
Ada dua cara untuk melakukan migrasi skema tabel ke hosting:

* **Cara A (Melalui Terminal SSH cPanel - Sangat Direkomendasikan)**:
  1. Jika paket hosting RumahWeb Anda mendukung fitur Terminal/SSH, buka menu **Terminal** di cPanel.
  2. Jalankan perintah berikut secara berurutan:
     ```bash
     cd /home/username/guruhub_app
     php artisan migrate --force
     php artisan db:seed --force
     ```

* **Cara B (Melalui Ekspor-Impor phpMyAdmin)**:
  1. Di komputer lokal, buka phpMyAdmin Anda. Pilih database `guruhub`, lalu klik tab **Export** dan klik **Go** untuk mengunduh file `.sql`.
  2. Buka cPanel RumahWeb Anda, lalu pilih menu **phpMyAdmin**.
  3. Pilih database baru Anda (`namauser_guruhubdb`), klik tab **Import**, pilih file `.sql` lokal Anda, lalu klik **Import / Go**.

### Langkah 8: Konfigurasi Symbolic Link Storage
Agar file raport PDF dan aset yang diunggah dapat diakses dari web browser:
1. Buka menu **Cron Jobs** di cPanel.
2. Tambahkan tugas kron sekali jalan dengan waktu eksekusi setiap menit, lalu masukkan perintah symlink ini:
   ```bash
   ln -s /home/username/guruhub_app/storage/app/public /home/username/public_html/storage
   ```
3. Klik **Add New Cron Job**. Tunggu 1 menit hingga tautan simbolik terbentuk, lalu **Hapus** cronjob tersebut agar tidak berjalan berulang-ulang.

### Langkah 9: Konfigurasi Cron Job untuk Scheduler Laravel (Wajib)
Agar fitur otomatisasi pembersihan logs notifikasi harian dan peringatan draft jurnal berjalan otomatis:
1. Buka menu **Cron Jobs** di cPanel.
2. Pada bagian *Common Settings*, pilih **Once Per Minute (* * * * *)**.
3. Di bagian kolom **Command**, masukkan perintah pemanggilan scheduler Laravel:
   ```bash
   /usr/local/bin/php /home/username/guruhub_app/artisan schedule:run >> /dev/null 2>&1
   ```
   *(Catatan: Path `/usr/local/bin/php` adalah standar RumahWeb. Jika tidak berjalan, Anda bisa mengecek versi PHP yang aktif di menu "Select PHP Version" atau menanyakan path PHP CLI ke layanan dukungan RumahWeb).*

---

## ⚡ Optimalisasi & Penanganan Masalah (Troubleshooting)

1. **Error 500 (Internal Server Error)**:
   * Periksa versi PHP hosting Anda di cPanel. Pastikan minimal menggunakan **PHP 8.2**.
   * Periksa perizinan folder (*folder permission*). Pastikan folder `/home/username/guruhub_app/storage` dan seluruh subfoldernya memiliki hak akses **`775`** atau **`755`**. Berkas PHP harus berseri **`644`**.

2. **Membersihkan Cache Setelah Update Kode**:
   Jika Anda melakukan perubahan kode di hosting, pastikan menghapus cache sistem agar perubahannya segera aktif. Jalankan command ini melalui menu Terminal cPanel:
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```
