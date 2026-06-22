# Panduan Deployment GuruHub ke Web Hosting (cPanel / RumahWeb)

RumahWeb dan sebagian besar shared hosting menggunakan panel administrasi **cPanel** dengan server berbasis Linux. Demi alasan keamanan, **JANGAN UPLOAD seluruh berkas Laravel ke dalam folder `public_html`**. 

Ikuti panduan terstruktur di bawah ini untuk memisahkan core Laravel (di luar `public_html`) dengan file publik, serta cara mengatasi batasan shared hosting (seperti `proc_open` disabled).

---

## 🛠️ Prasyarat Hosting
* **Versi PHP**: Minimal **PHP 8.2** atau **PHP 8.3** (aktifkan melalui menu *Select PHP Version* atau *MultiPHP Manager* di cPanel).
* **Ekstensi PHP Wajib**: `pdo_mysql`, `mbstring`, `openssl`, `xml`, `gd`, `zip`, dan `fileinfo`.
* **Database**: MySQL atau MariaDB.

---

## 📋 Langkah-Langkah Deployment

### Langkah 1: Persiapan Berkas di Komputer Lokal
1. Pastikan Anda telah mengompilasi aset CSS & JS untuk produksi di komputer lokal Anda:
   ```bash
   npm run build
   ```
2. Buat arsip `.zip` dari seluruh direktori proyek GuruHub Anda. **Kecualikan** folder/berkas berikut agar ukuran berkas tidak terlalu besar:
   * `node_modules/`
   * `tests/`
   * `old-backend/` dan `old-frontend/` (folder cadangan)
   * `.git/` dan `.github/`
   * `storage/framework/cache/data/*`
   * `storage/logs/*.log`

### Langkah 2: Unggah dan Ekstrak Berkas di cPanel
1. Masuk ke **cPanel RumahWeb** Anda.
2. Buka menu **File Manager**.
3. Pastikan Anda berada di direktori home Anda (misal: `/home/username/`, **di luar** `public_html`).
4. Buat folder baru dengan nama **`guruhub_app`** (Path lengkap: `/home/username/guruhub_app`).
5. Unggah berkas `.zip` yang sudah Anda buat ke dalam folder `/home/username/guruhub_app/`, lalu klik kanan dan pilih **Extract**.

### Langkah 3: Pindahkan Berkas Publik ke `public_html`
1. Buka folder `/home/username/guruhub_app/public/` di File Manager.
2. Pilih seluruh file dan folder di dalamnya (termasuk berkas `.htaccess`, `index.php`, dan folder `build`).
3. Klik tombol **Move** (Pindah) di bagian atas File Manager cPanel, arahkan target tujuan ke **`/public_html`** (atau sub-domain Anda, misal `/public_html/guruhub`).
4. Sekarang, folder `/home/username/guruhub_app/public/` Anda harus kosong, dan seluruh isinya telah berpindah ke `/home/username/public_html/`.

### Langkah 4: Modifikasi Path pada `public_html/index.php`
Karena struktur folder inti telah dipindahkan, kita harus mengarahkan bootstrapper Laravel ke direktori baru.
1. Buka folder `public_html/` Anda.
2. Klik kanan pada berkas **`index.php`** dan pilih **Edit**.
3. Cari baris pemuatan autoloader dan ubah path-nya:
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

### Langkah 5: Konfigurasi Database MySQL di cPanel
1. Di cPanel, cari dan klik menu **MySQL® Database Wizard**.
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

### Langkah 7: Migrasi Database ke Live Hosting
Ada dua cara untuk melakukan migrasi skema tabel ke hosting:

* **Cara A (Melalui Terminal SSH cPanel - Sangat Direkomendasikan)**:
  1. Buka menu **Terminal** di cPanel.
  2. Jalankan perintah berikut secara berurutan:
     ```bash
     cd /home/username/guruhub_app
     php artisan migrate --force
     php artisan db:seed --force
     ```

* **Cara B (Melalui Ekspor-Impor phpMyAdmin)**:
  1. Di komputer lokal, buka phpMyAdmin Anda. Pilih database `guruhub`, lalu klik tab **Export** dan unduh file `.sql`.
  2. Buka cPanel hosting Anda, pilih menu **phpMyAdmin**.
  3. Pilih database baru Anda (`namauser_guruhubdb`), klik tab **Import**, pilih file `.sql` lokal Anda, lalu jalankan import.

### Langkah 8: Instalasi Dependensi Baru & Penanganan `proc_open` (Penting)
Jika Anda melakukan pembaruan kode yang membutuhkan dependensi baru (seperti library Excel), Anda perlu melakukan instalasi di hosting. Namun, shared hosting biasanya menonaktifkan fungsi `proc_open` yang membuat Composer gagal di akhir proses.

**Solusinya adalah melewati eksekusi script post-autoload dengan parameter `--no-scripts`**:

1. Buka **Terminal** cPanel.
2. Masuk ke folder proyek Anda:
   ```bash
   cd /home/username/guruhub_app
   ```
3. Unduh Composer mandiri (jika perintah `composer` global tidak terdaftar):
   ```bash
   curl -sS https://getcomposer.org/installer | php
   ```
4. Jalankan perintah instalasi berikut:
   ```bash
   php composer.phar install --no-dev --no-scripts
   ```
   *(Atau sesuaikan versi PHP jika php terminal Anda masih versi lama, misal: `/usr/local/bin/ea-php82 composer.phar install --no-dev --no-scripts`)*

### Langkah 9: Konfigurasi Symbolic Link Storage
Agar file raport PDF dan aset yang diunggah dapat diakses dari web browser:
1. Buka menu **Cron Jobs** di cPanel.
2. Tambahkan tugas kron sekali jalan dengan waktu eksekusi setiap menit, lalu masukkan perintah symlink ini:
   ```bash
   ln -s /home/username/guruhub_app/storage/app/public /home/username/public_html/storage
   ```
3. Klik **Add New Cron Job**. Tunggu 1 menit hingga tautan simbolik terbentuk di `/public_html/storage`, lalu **Hapus** cronjob tersebut agar tidak berjalan berulang-ulang.

### Langkah 10: Konfigurasi Cron Job untuk Scheduler Laravel (Wajib)
Agar fitur otomatisasi pembersihan logs notifikasi harian dan peringatan draft jurnal berjalan otomatis:
1. Buka menu **Cron Jobs** di cPanel.
2. Pada bagian *Common Settings*, pilih **Once Per Minute (* * * * *)**.
3. Di bagian kolom **Command**, masukkan perintah pemanggilan scheduler Laravel:
   ```bash
   /usr/local/bin/php /home/username/guruhub_app/artisan schedule:run >> /dev/null 2>&1
   ```

---

## ⚡ Optimalisasi & Penanganan Masalah (Troubleshooting)

1. **Error 500 (Internal Server Error)**:
   * Periksa versi PHP hosting Anda di cPanel. Pastikan minimal menggunakan **PHP 8.2**.
   * Periksa perizinan folder (*folder permission*). Pastikan folder `/home/username/guruhub_app/storage` dan seluruh subfoldernya memiliki hak akses **`775`** atau **`755`**. Berkas PHP harus memiliki hak akses **`644`**.
   * Ubah sementara `APP_DEBUG=true` di berkas `.env` untuk melihat pesan kesalahan detail di layar browser.

2. **Membersihkan Cache Setelah Update Kode**:
   Jika Anda melakukan perubahan kode di hosting, pastikan menghapus cache sistem agar perubahannya segera aktif. Jalankan command ini melalui menu Terminal cPanel:
   ```bash
   php artisan config:cache
   ```
   Atau untuk membersihkan seluruh cache:
   ```bash
   php artisan cache:clear && php artisan config:clear && php artisan view:clear
   ```
