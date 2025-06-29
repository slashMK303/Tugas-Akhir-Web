# Tugas-Akhir-Web

Ini adalah repositori untuk proyek "Tugas-Akhir-Web". Proyek ini adalah sebuah aplikasi web yang dibangun dengan PHP dan CSS.

## Fitur Utama

* **Sistem Administrasi:** Modul untuk pengelolaan data dan fungsi oleh administrator.
* **Autentikasi Pengguna:** Fungsionalitas untuk login, registrasi, dan manajemen sesi pengguna.
* **Manajemen Data Pegawai:** Sistem untuk mengelola informasi dan data terkait karyawan.
* **Manajemen Data Pembeli:** Sistem untuk mengelola informasi dan data terkait pelanggan atau pembeli.
* **Tampilan Produk/Barang:** Fitur untuk menampilkan daftar atau detail produk/barang yang tersedia.

## Teknologi yang Digunakan

* **PHP:** Bahasa pemrograman utama yang digunakan untuk logika server-side (99.4%).
* **CSS:** Digunakan untuk styling dan tampilan antarmuka pengguna (0.6%).

## Struktur Proyek

Repositori ini mencakup folder dan file berikut:

* `/admin`: Berisi file-file yang berkaitan dengan panel atau fungsionalitas administrator.
* `/asset/img`: Direktori untuk menyimpan aset gambar.
* `/auth`: Berisi file-file terkait sistem autentikasi (login, register).
* `/config`: Berisi file konfigurasi aplikasi.
* `/css`: Direktori untuk file stylesheet CSS.
* `/pegawai`: Berisi file-file yang terkait dengan manajemen data pegawai.
* `/pembeli`: Berisi file-file yang terkait dengan manajemen data pembeli.
* `README.md`: Berkas ini (deskripsi proyek).
* `index.php`: Halaman utama atau titik masuk aplikasi web.
* `lihat_barang.php`: File untuk menampilkan informasi barang/produk.

## Instalasi

Karena detail instalasi tidak tersedia, berikut adalah langkah-langkah umum yang mungkin diperlukan untuk menjalankan proyek PHP semacam ini:

1.  **Kloning Repositori:**
    ```bash
    git clone https://github.com/slashMK303/Tugas-Akhir-Web.git
    cd Tugas-Akhir-Web
    ```
2.  **Siapkan Lingkungan Server Lokal:**
    Pastikan Anda memiliki server web (misalnya Apache atau Nginx) dengan PHP terinstal, serta database (misalnya MySQL atau MariaDB). Anda bisa menggunakan XAMPP, WAMP, atau Laragon.
3.  **Konfigurasi Database:**
    * Buat database baru di server database Anda.
    * Perbarui file konfigurasi database di dalam folder `config` (jika ada) dengan kredensial database Anda (nama database, username, password).
4.  **Impor Database (Jika Ada):**
    Jika ada file dump database (misalnya `.sql`), impor file tersebut ke database yang telah Anda buat.
5.  **Akses Aplikasi:**
    Buka browser web Anda dan navigasikan ke direktori proyek di server lokal Anda (misalnya `http://localhost/Tugas-Akhir-Web`).

## Penggunaan

Detail penggunaan spesifik aplikasi tidak tersedia. Setelah instalasi berhasil, Anda dapat menjelajahi halaman `index.php` dan bagian-bagian lain seperti `admin`, `pegawai`, atau `pembeli` untuk memahami alur kerja aplikasi.
