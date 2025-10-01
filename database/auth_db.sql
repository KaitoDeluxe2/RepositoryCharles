-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 01 Okt 2025 pada 10.30
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `auth_db`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `buku`
--

CREATE TABLE `buku` (
  `id` int(11) NOT NULL,
  `judul` varchar(255) NOT NULL,
  `penulis` varchar(100) DEFAULT NULL,
  `penerbit` varchar(100) DEFAULT NULL,
  `tahun_terbit` year(4) DEFAULT NULL,
  `isbn` varchar(20) DEFAULT NULL,
  `kategori` varchar(50) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `cover_path` varchar(255) DEFAULT NULL,
  `file_path` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `buku`
--

INSERT INTO `buku` (`id`, `judul`, `penulis`, `penerbit`, `tahun_terbit`, `isbn`, `kategori`, `deskripsi`, `cover_path`, `file_path`) VALUES
(2, 'Jaringan Komputer Modern', 'Budi Hartono', 'Graha Ilmu', '2020', '978-979-756-890-1', 'Jaringan', 'Pelajari konsep dasar jaringan, protokol TCP/IP, dan keamanan jaringan dalam panduan komprehensif ini.', 'Gambar/covers/1758677443_Gemini_Generated_Image_d1271sd1271sd127.png', 'ebooks/jaringan_komputer.pdf'),
(3, 'Algoritma dan Struktur Data', 'Citra Lestari', '', '0000', '', '', 'Menguasai logika pemrograman melalui pemahaman mendalam tentang algoritma esensial dan struktur data yang efisien.', 'Gambar/covers/1757568511_struktur_gambar.png', 'ebooks/algoritma_struktur_data.pdf'),
(4, 'Panduan Praktis UI/UX Design', 'Dewi Anggraini', '', '0000', '', '', 'Langkah demi langkah merancang antarmuka pengguna yang intuitif dan pengalaman pengguna yang memuaskan untuk aplikasi web dan mobile.', 'Gambar/covers/1757565744_ui_ux_gambar.png', 'ebooks/ui_ux_design.pdf'),
(5, 'Python untuk Sains Data', 'Eko Prasetyo', '', '0000', '', 'Pendidikan', 'Memanfaatkan kekuatan Python dan library seperti Pandas dan Matplotlib untuk melakukan analisis dan visualisasi data secara efektif.', 'Gambar/covers/1757564727_Pyton.png', 'ebooks/python_data_sains.pdf'),
(10, 'Informatikaa', 'Tim Kemendikbudristek', 'Kemendikbudristek', '2021', '978-602-244-506-7', 'Pendidikan', 'Buku ajar resmi untuk siswa SMA yang mencakup materi Berpikir Komputasional (BK), Teknologi Informasi dan Komunikasi (TIK), Sistem Komputer (SK), Jaringan Komputer dan Internet (JKI), dan Analisis Data (AD).', 'Gambar/covers/1757562808_coverinformatika.png', 'ebooks/1753345280_Informatika-BS-KLS-X.pdf');

-- --------------------------------------------------------

--
-- Struktur dari tabel `diskusi`
--

CREATE TABLE `diskusi` (
  `id` int(11) NOT NULL,
  `buku_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `komentar` text NOT NULL,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `tanggal` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `diskusi`
--

INSERT INTO `diskusi` (`id`, `buku_id`, `user_id`, `username`, `komentar`, `parent_id`, `tanggal`) VALUES
(2, 10, 5, 'Andi Wijaya', 'halo admin', 0, '2025-08-11 04:30:28'),
(3, 10, 4, 'admin_utama', 'test', 0, '2025-08-27 02:55:14'),
(4, 10, 5, 'Andi Wijaya', 'mantap', 0, '2025-09-11 03:59:50'),
(5, 4, 4, 'admin_utama', 'HALO SAYA ADMIN\r\n', 0, '2025-09-22 09:12:11'),
(7, 2, 5, 'Andi Wijaya', 'kommputer keren', 0, '2025-09-26 03:58:21'),
(8, 10, 4, 'admin_utama', 'halo semua aku admin', 0, '2025-09-29 05:48:21'),
(9, 10, 4, 'admin_utama', '[spoiler][/spoiler]', 0, '2025-09-29 05:50:06'),
(10, 10, 4, 'admin_utama', 'mantap makasih', 2, '2025-09-29 05:55:50'),
(11, 5, 4, 'admin_utama', 'halo namaku edu\r\n', 0, '2025-09-29 05:59:24'),
(12, 5, 4, 'admin_utama', 'halo juga namaku edward', 11, '2025-09-29 05:59:36'),
(13, 2, 4, 'admin_utama', 'oke\r\n', 7, '2025-09-29 08:49:10'),
(14, 10, 4, 'admin_utama', 'p', 2, '2025-10-01 08:26:58');

-- --------------------------------------------------------

--
-- Struktur dari tabel `mahasiswa_resmi`
--

CREATE TABLE `mahasiswa_resmi` (
  `nim` varchar(20) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `mahasiswa_resmi`
--

INSERT INTO `mahasiswa_resmi` (`nim`, `nama_lengkap`) VALUES
('3312301001', 'Budi Santoso'),
('3312301003', 'Andi Wijaya'),
('3314567891', 'Charles Jason');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `nim` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') NOT NULL DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `name`, `username`, `email`, `nim`, `password`, `role`) VALUES
(2, NULL, 'Budi Santoso', 'nelsonmeylina@gmail.com', '3312301001', '$2y$10$Tx2duiM4Pz/fHHJI5aOMheNef1KrK8QnizeP/N0TJGjgY5n8ks.3.', 'user'),
(4, NULL, 'admin_utama', 'admin2@gmail.com', NULL, '$2y$10$vauZmBosJvPFVtAgVxSyXON10lQ0V/MxqPD/MfbaFSUXC6VuOuODi', 'admin'),
(5, NULL, 'Andi Wijaya', 'andi@gmail.com', '3312301003', '$2y$10$844riWtfKQ/yzlpzuiJFKuKsJTHqrMQfohhc07TmX0lcXqiTVobUe', 'user');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `buku`
--
ALTER TABLE `buku`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `diskusi`
--
ALTER TABLE `diskusi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `buku_id` (`buku_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `mahasiswa_resmi`
--
ALTER TABLE `mahasiswa_resmi`
  ADD PRIMARY KEY (`nim`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `nim` (`nim`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `buku`
--
ALTER TABLE `buku`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `diskusi`
--
ALTER TABLE `diskusi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
