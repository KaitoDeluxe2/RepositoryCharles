# Perpustakaan Digital - Politeknik Negeri Batam

Selamat datang di repositori Perpustakaan Digital, sebuah sistem manajemen perpustakaan berbasis web yang dirancang untuk memberikan kemudahan akses dan pengelolaan koleksi buku secara digital. Proyek ini dibangun sebagai bagian dari program magang dan portofolio pengembangan web.

Aplikasi ini memiliki dua peran utama: **Admin** untuk manajemen data dan **User** untuk mengakses koleksi buku serta berpartisipasi dalam diskusi.

## Tampilan Aplikasi (Screenshot)

<img width="1920" height="1080" alt="image" src="https://github.com/user-attachments/assets/a382aca7-12eb-41bf-8335-f1badeb1466b" />


## Fitur Utama

### Untuk Pengguna (User)

  - 🔐 **Sistem Otentikasi**: Pengguna dapat mendaftar (register) dan masuk (login) ke dalam sistem.
  - 📚 **Dashboard Koleksi**: Melihat koleksi buku terbaru yang tersedia di perpustakaan.
  - 📖 **Detail Buku**: Melihat informasi lengkap mengenai sebuah buku, termasuk judul, penulis, dan deskripsi.
  - 💬 **Forum Diskusi**: Berpartisipasi dalam diskusi untuk setiap buku, memberikan komentar, dan melihat pendapat pengguna lain.
  - 👤 **Manajemen Akun**: Pengguna dapat memperbarui informasi profil mereka seperti nama dan email.

### Untuk Administrator (Admin)

  - 🛠️ **Dashboard Admin**: Halaman utama yang menampilkan ringkasan data (total pengguna, total buku, dll).
  - 📖 **Manajemen Buku (CRUD)**: Admin dapat menambah, melihat, mengedit (update), dan menghapus koleksi buku.
  - 👥 **Manajemen Pengguna**: Mengelola akun pengguna yang terdaftar, termasuk mengubah peran (role) dan menghapus akun.
  - 🎓 **Manajemen Mahasiswa Resmi**: Mengelola daftar NIM mahasiswa yang diizinkan untuk mendaftar, memastikan hanya mahasiswa resmi yang bisa membuat akun.

## Teknologi yang Digunakan

  - **Backend**: PHP
  - **Database**: MySQL
  - **Frontend**: HTML, CSS, Bootstrap 5
  - **Server Lokal**: XAMPP

## Cara Instalasi & Setup Lokal

Untuk menjalankan proyek ini di komputer Anda, ikuti langkah-langkah berikut:

1.  **Clone Repositori**

    ```bash
    git clone https://github.com/KaitoDeluxe2/RepositoryCharles.git
    ```

2.  **Pindahkan Folder Proyek**

      - Pindahkan folder `RepositoryCharles` yang sudah di-clone ke dalam direktori `htdocs` di dalam folder instalasi XAMPP Anda. (Contoh: `C:\xampp\htdocs\RepositoryCharles`)

3.  **Setup Database**

      - Buka **phpMyAdmin** dari control panel XAMPP (`http://localhost/phpmyadmin`).
      - Buat database baru dengan nama `auth_db`.
      - Impor file `.sql` yang berisi struktur tabel ke dalam database `auth_db`. *(Anda perlu menyediakan file ini)*.

4.  **Konfigurasi Koneksi Database**

      - Buka file `includes/db.php`.
      - Pastikan detail koneksi (`$host`, `$user`, `$pass`, `$db`) sudah sesuai dengan konfigurasi XAMPP Anda. Konfigurasi default biasanya sudah benar.
        ```php
        <?php
        $host = "localhost";
        $user = "root";
        $pass = ""; // Kosongkan jika tidak ada password
        $db   = "auth_db";

        $conn = mysqli_connect($host, $user, $pass, $db);

        if (!$conn) {
          die("Koneksi gagal: " . mysqli_connect_error());
        }
        ?>
        ```

5.  **Jalankan Aplikasi**

      - Nyalakan **Apache** dan **MySQL** dari XAMPP Control Panel.
      - Buka browser Anda dan akses:
        ```
        http://localhost/RepositoryCharles/
        ```
## Import Database

1. Buka terminal / command prompt.
2. Jalankan perintah:

   ```bash
   mysql -u root -p auth_db < database/auth_db.sql

## Dibuat Oleh

  - **[Kaitooooo (KaitoDeluxe2)](https://www.google.com/search?q=https://github.com/KaitoDeluxe2)**

-----

