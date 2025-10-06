-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 06 Okt 2025 pada 06.34
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
(4, 'Panduan Praktis UI/UX Design', 'Dewi Anggraini', '', '0000', '', 'Pendidikan', 'Langkah demi langkah merancang antarmuka pengguna yang intuitif dan pengalaman pengguna yang memuaskan untuk aplikasi web dan mobile.', 'Gambar/covers/1757565744_ui_ux_gambar.png', 'ebooks/ui_ux_design.pdf'),
(5, 'Python untuk Sains Data', 'Eko Prasetyo', '', '0000', '', 'Pendidikan', 'Memanfaatkan kekuatan Python dan library seperti Pandas dan Matplotlib untuk melakukan analisis dan visualisasi data secara efektif.', 'Gambar/covers/1757564727_Pyton.png', 'ebooks/python_data_sains.pdf'),
(10, 'Informatikaa', 'Tim Kemendikbudristek', 'Kemendikbudristek', '2021', '978-602-244-506-7', 'Pendidikan', 'Buku ajar resmi untuk siswa SMA yang mencakup materi Berpikir Komputasional (BK), Teknologi Informasi dan Komunikasi (TIK), Sistem Komputer (SK), Jaringan Komputer dan Internet (JKI), dan Analisis Data (AD).', 'Gambar/covers/1757562808_coverinformatika.png', 'ebooks/1753345280_Informatika-BS-KLS-X.pdf'),
(100, 'Komik Petualangan Si Ucup', 'Andi Pratama', 'Manga Nusantara', '2019', '978-602-123-000-1', 'Komik', 'Komik kocak tentang Ucup yang tersesat di dunia paralel.', 'Gambar/Covers/1759309456_ucup.png', 'ebooks/ucup_petualangan.pdf'),
(101, 'Romansa Kopi Senjaa', 'Dina Laras', 'Sweetline Books', '2021', '978-602-123-000-2', 'Romansa', 'Cerita ringan tentang dua orang barista yang jatuh cinta.', 'Gambar/Covers/1759309553_kopii.png', 'ebooks/romansa_kopi.pdf'),
(102, 'Horor Malam Jumats', 'Bayu Saputra', 'Dark Moon', '2020', '978-602-123-000-3', 'Horor', 'Kumpulan kisah mistis malam jumat dari berbagai daerah.', 'Gambar/Covers/1759310359_jumat.png', 'ebooks/horor_malam_jumat.pdf'),
(103, 'Fantasi Negeri Awann', 'Sari Dewi', 'Aurora Press', '2018', '978-602-123-000-4', 'Fantasi', 'Petualangan di negeri awan yang penuh makhluk ajaib.', 'Gambar/Covers/1759309356_awan.png', 'ebooks/fantasi_negeri_awan.pdf'),
(104, 'Thriller Kota Gelap', 'Raka Nugraha', 'Noir Line', '2022', '978-602-123-000-5', 'Thriller', 'Kisah detektif yang harus memecahkan kasus konspirasi di kota penuh rahasia.', 'Gambar/Covers/1759309266_horor.png', 'ebooks/thriller_kota_gelap.pdf'),
(105, 'Komedi Anak Kos', 'Teguh Wibowo', 'Santai Press', '2020', '978-602-123-000-6', 'Komedi', 'Kumpulan cerita kocak kehidupan anak kos dengan segala dramanya.', 'Gambar/Covers/1759310640_komedi anak kos.png', 'ebooks/komedi_anak_kos.pdf'),
(106, 'Petualangan Robot Kecil', 'Lina Prameswari', 'Future Kidss', '2021', '978-602-123-000-7', 'Anak-Anak', 'Kisah seru robot kecil yang belajar arti persahabatan sambil menjelajah kota.', 'Gambar/Covers/1759310593_robot.png', 'ebooks/petualangan_robot.pdf'),
(108, 'Dune: Sang Mesias Padang Pasir', 'Frank Herbert', 'Gramedia Pustaka Utama', '2021', '978-602-06-5263-4', 'Fiksi Ilmiah', 'Kisah epik tentang politik, agama, dan kekuasaan di planet padang pasir Arrakis. Paul Atreides harus menavigasi takdirnya sebagai pemimpin yang dinubuatkan sambil menghadapi konspirasi dari berbagai faksi galaksi.', 'Gambar/Covers/1759374833_unnamed (1).png', 'ebooks/dune_ebook.pdf'),
(109, 'Pembunuhan di Orient Express', 'Agatha Christie', 'Gramedia Pustaka Utama', '2017', '978-602-03-3975-7', 'Misteri', 'Detektif legendaris Hercule Poirot terjebak dalam kereta yang tertimbun salju dengan seorang korban pembunuhan. Semua penumpang adalah tersangka, dan Poirot harus memecahkan kasusnya sebelum si pembunuh bertindak lagi.', 'Gambar/Covers/1759374840_unnamed.png', 'ebooks/orient_express_ebook.pdf');

-- --------------------------------------------------------

--
-- Struktur dari tabel `comment_dislikes`
--

CREATE TABLE `comment_dislikes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `comment_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `comment_dislikes`
--

INSERT INTO `comment_dislikes` (`id`, `user_id`, `comment_id`) VALUES
(1, 6, 26);

-- --------------------------------------------------------

--
-- Struktur dari tabel `comment_likes`
--

CREATE TABLE `comment_likes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `comment_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `comment_likes`
--

INSERT INTO `comment_likes` (`id`, `user_id`, `comment_id`) VALUES
(7, 6, 20),
(5, 6, 25),
(4, 6, 28),
(8, 6, 29),
(1, 7, 20),
(2, 7, 28);

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
  `tanggal` timestamp NOT NULL DEFAULT current_timestamp(),
  `likes` int(11) NOT NULL DEFAULT 0,
  `dislikes` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `diskusi`
--

INSERT INTO `diskusi` (`id`, `buku_id`, `user_id`, `username`, `komentar`, `parent_id`, `tanggal`, `likes`, `dislikes`) VALUES
(2, 10, 5, 'Andi Wijaya', 'halo admin', 0, '2025-08-11 04:30:28', 0, 0),
(4, 10, 5, 'Andi Wijaya', 'mantap', 0, '2025-09-11 03:59:50', 0, 0),
(5, 4, 4, 'admin_utama', 'HALO SAYA ADMIN\r\n', 0, '2025-09-22 09:12:11', 0, 0),
(7, 2, 5, 'Andi Wijaya', 'kommputer keren', 0, '2025-09-26 03:58:21', 0, 0),
(8, 10, 4, 'admin_utama', 'halo semua aku admin', 0, '2025-09-29 05:48:21', 0, 0),
(10, 10, 4, 'admin_utama', 'mantap makasih', 2, '2025-09-29 05:55:50', 0, 0),
(11, 5, 4, 'admin_utama', 'halo namaku edu\r\n', 0, '2025-09-29 05:59:24', 0, 0),
(12, 5, 4, 'admin_utama', 'halo juga namaku edward', 11, '2025-09-29 05:59:36', 0, 0),
(13, 2, 4, 'admin_utama', 'oke\r\n', 7, '2025-09-29 08:49:10', 0, 0),
(15, 104, 4, 'admin_utama', 'sepsi lah', 0, '2025-10-01 09:08:27', 0, 0),
(16, 104, 4, 'admin_utama', 'p', 0, '2025-10-01 09:09:20', 0, 0),
(17, 106, 6, 'EDU', 'Robot yang keren dan penuh semangat😊', 0, '2025-10-02 02:11:18', 0, 0),
(18, 103, 6, 'EDU', 'halo aku edu', 0, '2025-10-02 02:29:26', 0, 0),
(19, 106, 8, 'josep', 'keren edu', 17, '2025-10-02 02:38:03', 0, 0),
(20, 109, 9, 'santi', 'hai', 0, '2025-10-02 03:40:45', 2, 0),
(21, 10, 6, 'EDU', 'hai', 14, '2025-10-02 03:55:18', 0, 0),
(22, 10, 6, 'EDU', 'hai', 21, '2025-10-02 03:55:25', 0, 0),
(23, 10, 6, 'EDU', 'hai\r\n', 22, '2025-10-02 03:55:34', 0, 0),
(24, 10, 6, 'EDU', 'p', 23, '2025-10-02 03:59:18', 0, 0),
(25, 109, 7, 'Admin', 'a', 20, '2025-10-02 03:59:50', 1, 0),
(26, 109, 7, 'Admin', 'a', 25, '2025-10-02 03:59:54', 0, 1),
(27, 10, 7, 'Admin', 'pp', 10, '2025-10-02 04:03:28', 0, 0),
(28, 109, 7, 'Admin', 'halo aku admin sekarang', 0, '2025-10-03 04:20:01', 2, 0),
(29, 109, 6, 'EDU', 'halo', 0, '2025-10-03 04:25:39', 1, 0),
(30, 109, 6, 'EDU', 'mantap', 28, '2025-10-03 04:32:16', 0, 0),
(31, 109, 6, 'EDU', 'halo', 0, '2025-10-03 06:29:23', 0, 0),
(32, 109, 6, 'EDU', 'halo namaku edward', 30, '2025-10-03 06:35:27', 0, 0),
(33, 10, 6, 'EDU', 'hai', 4, '2025-10-03 06:49:51', 0, 0),
(34, 109, 6, 'EDU', 'oi', 28, '2025-10-03 06:57:18', 0, 0),
(35, 109, 7, 'Admin', 'hai', 0, '2025-10-03 07:24:39', 0, 0),
(36, 109, 6, 'EDU', 'halo pendapatmu sangat berguna terimakassih ya', 25, '2025-10-06 02:50:42', 0, 0);

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
('1001', 'Admin'),
('1111', 'santi'),
('1212', 'josep'),
('123456789', 'edu'),
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
  `role` enum('user','admin') NOT NULL DEFAULT 'user',
  `avatar_seed` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `name`, `username`, `email`, `nim`, `password`, `role`, `avatar_seed`) VALUES
(2, NULL, 'Budi Santoso', 'nelsonmeylina@gmail.com', '3312301001', '$2y$10$Tx2duiM4Pz/fHHJI5aOMheNef1KrK8QnizeP/N0TJGjgY5n8ks.3.', 'user', '503a2402c2460e035c7cd3ce31f84f9307b7e51b6c6f60b1b66723bc0ea9f641'),
(4, NULL, 'admin_utama', 'admin2@gmail.com', NULL, '$2y$10$vauZmBosJvPFVtAgVxSyXON10lQ0V/MxqPD/MfbaFSUXC6VuOuODi', 'admin', '801b9544c2318d06763ece80301da521d819f90bb2f0cb8f007ac5a73b36911e'),
(5, NULL, 'Andi Wijaya', 'andi@gmail.com', '3312301003', '$2y$10$844riWtfKQ/yzlpzuiJFKuKsJTHqrMQfohhc07TmX0lcXqiTVobUe', 'user', 'dcb6d4c36d5bfdad726264f385e02de8c783f83a8c1ef173b6346f0dc6ee6963'),
(6, NULL, 'EDU', 'edumail@gmail.com', '123456789', '$2y$10$gFgC2P3JvLcAHzKc5ROcqewwtUAPFpPPOvE4Re5U36yoviDNdBDd.', 'user', '0dd477424fd82bf30f582fc7300000d94a6937b7bd82522f16f0763406f476b8'),
(7, NULL, 'Admin', 'admin@gmail.com', 'N/A', '$2y$10$JGkJRxwg6Jefi7deJRo.MOqT.qWKmIStFkvxAfaM3UUdf2sQwsCJa', 'admin', '31d04a00006967cb860616af853232491da85448e05a87c42d3d1ac5691eddef'),
(9, NULL, 'santi', 'santi@gmail.com', '1111', '$2y$10$YHU8jT0BNL752PAuJZg6l.hNeN1PFAIK8eCEo40BcNZbDQWdJWvQy', 'user', '48671e288b1591efe62619bb7871f40adb3a412e4f049a8f07d0f4997f39de7d');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `buku`
--
ALTER TABLE `buku`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `comment_dislikes`
--
ALTER TABLE `comment_dislikes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_comment_dislike` (`user_id`,`comment_id`);

--
-- Indeks untuk tabel `comment_likes`
--
ALTER TABLE `comment_likes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_comment` (`user_id`,`comment_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=110;

--
-- AUTO_INCREMENT untuk tabel `comment_dislikes`
--
ALTER TABLE `comment_dislikes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `comment_likes`
--
ALTER TABLE `comment_likes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `diskusi`
--
ALTER TABLE `diskusi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
