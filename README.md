# 📘 Panduan Penggunaan Sistem Keuangan UKM

Dokumen ini berisi panduan lengkap penggunaan **Sistem Keuangan UKM** untuk membantu Anda mengelola pembukuan usaha secara sederhana, cepat, dan efisien.

---

## 📑 Daftar Isi

* [Persiapan & Instalasi](#1-persiapan--instalasi)
* [Akses Sistem & Akun](#2-akses-sistem--akun)
* [Manajemen Toko (Store)](#3-manajemen-toko-store)
* [Manajemen Akun Keuangan (Kategori)](#4-manajemen-akun-keuangan-kategori)
* [Pencatatan Transaksi](#5-pencatatan-transaksi)
* [Laporan Keuangan](#6-laporan-keuangan)
* [Profil Pengguna](#profil-pengguna)

---

## 1. Persiapan & Instalasi

> Bagian ini ditujukan untuk **administrator/developer** yang ingin menjalankan aplikasi di komputer lokal.

### **Langkah Instalasi**

1. **Clone repository dan masuk ke direktori proyek**

   ```bash
   git clone <repository-url>
   cd project-folder
   ```

2. **Install dependensi**

   ```bash
   composer install
   npm install && npm run build
   ```

3. **Salin file konfigurasi `.env`**

   ```bash
   cp .env.example .env
   ```

4. **Atur koneksi database** di file `.env`
   Sesuaikan bagian:

   ```
   DB_DATABASE=
   DB_USERNAME=
   DB_PASSWORD=
   ```

5. **Generate application key & migrasi database**

   ```bash
   php artisan key:generate
   php artisan migrate
   ```

6. **Menjalankan server lokal**

   ```bash
   php artisan serve
   ```

7. **Akses aplikasi**

   ```
   http://localhost:8000
   ```

---

## 2. Akses Sistem & Akun

### **Registrasi**

* Buka halaman awal aplikasi.
* Pilih menu **Register**.
* Isi **Nama, Email, Password**.
* Klik **Daftar**.

### **Login**

* Masukkan **email** dan **password** yang telah terdaftar.
* Anda akan diarahkan ke **Dashboard**.

### **Dashboard**

Menampilkan ringkasan aktivitas keuangan usaha Anda.

---

## 3. Manajemen Toko (Store)

Sistem mendukung **multiple store** (beberapa usaha dalam satu akun).

### **Membuat Toko Baru**

* Masuk ke menu **Stores** (atau otomatis diminta membuat jika belum ada toko).
* Klik **Tambah Toko**.
* Isi data:

  * Nama Toko
  * Alamat (opsional)
  * Jenis Usaha (opsional)
  * Nomor Telepon (opsional)
* Klik **Simpan**.

### **Berpindah Toko (Switch Store)**

Jika Anda mengelola lebih dari satu toko, gunakan menu navigasi untuk memilih toko lain dan melihat pembukuan masing-masing.

---

## 4. Manajemen Akun Keuangan (Kategori)

Kategori digunakan untuk mengelompokkan pemasukan dan pengeluaran.

### **Menambah Akun (Kategori)**

* Masuk ke menu **Accounts**.
* Klik **Tambah Akun**.
* Isi:

  * Nama Akun (contoh: Penjualan Tunai, Biaya Listrik, Gaji Karyawan)
  * Tipe (Income / Expense)
  * Deskripsi (opsional)
* Klik **Simpan**.

💡 *Tips:* Buat kategori spesifik agar laporan lebih akurat dan mudah dianalisis.

---

## 5. Pencatatan Transaksi

### **Menambah Transaksi Baru**

* Masuk ke menu **Transactions**.
* Klik **Tambah Transaksi**.
* Isi form:

  * Tipe: **Pemasukan / Pengeluaran**
  * Akun: Pilih kategori sesuai tipe
  * Jumlah (Amount)
  * Tanggal Transaksi
  * Deskripsi
  * Upload Bukti Transaksi (JPG/PNG/PDF, max 5MB)
* Klik **Simpan**.

### **Melihat & Memfilter Transaksi**

Filter berdasarkan:

* Jenis transaksi
* Akun (kategori)
* Rentang tanggal
* Bulan/Tahun
* Keyword pencarian (berdasarkan deskripsi)

---

## 6. Laporan Keuangan

Akses melalui menu **Reports** untuk melihat laporan otomatis seperti:

* **Laporan Pemasukan (Income)**
* **Laporan Pengeluaran (Expense)**
* **Laba Rugi (Profit & Loss)**
* **Arus Kas (Cashflow)**

### **Ekspor PDF**

Semua laporan dapat diunduh dalam format PDF untuk arsip atau dicetak.

---

## Profil Pengguna

Untuk mengubah informasi akun atau password:

* Masuk ke menu **Profile** di pojok kanan atas aplikasi.
* Edit data sesuai kebutuhan.
