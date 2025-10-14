-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 14 Okt 2025 pada 04.06
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
-- Struktur dari tabel `book_ratings`
--

CREATE TABLE `book_ratings` (
  `id` int(11) NOT NULL,
  `buku_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `book_ratings`
--

INSERT INTO `book_ratings` (`id`, `buku_id`, `user_id`, `rating`) VALUES
(1, 109, 6, 3),
(2, 109, 7, 1),
(3, 10, 7, 1),
(4, 104, 7, 1);

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
  `file_path` varchar(255) NOT NULL,
  `total_rating` int(11) NOT NULL DEFAULT 0,
  `rating_count` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `buku`
--

INSERT INTO `buku` (`id`, `judul`, `penulis`, `penerbit`, `tahun_terbit`, `isbn`, `kategori`, `deskripsi`, `cover_path`, `file_path`, `total_rating`, `rating_count`) VALUES
(2, 'Jaringan Komputer Modern', 'Budi Hartono', 'Graha Ilmu', '2020', '978-979-756-890-1', 'Jaringan', 'Pelajari konsep dasar jaringan, protokol TCP/IP, dan keamanan jaringan dalam panduan komprehensif ini.', 'Gambar/covers/1758677443_Gemini_Generated_Image_d1271sd1271sd127.png', 'ebooks/jaringan_komputer.pdf', 0, 0),
(3, 'Algoritma dan Struktur Data', 'Citra Lestari', '', '0000', '', '', 'Menguasai logika pemrograman melalui pemahaman mendalam tentang algoritma esensial dan struktur data yang efisien.', 'Gambar/covers/1757568511_struktur_gambar.png', 'ebooks/algoritma_struktur_data.pdf', 0, 0),
(4, 'Panduan Praktis UI/UX Design', 'Dewi Anggraini', '', '0000', '', 'Pendidikan', 'Langkah demi langkah merancang antarmuka pengguna yang intuitif dan pengalaman pengguna yang memuaskan untuk aplikasi web dan mobile.', 'Gambar/covers/1757565744_ui_ux_gambar.png', 'ebooks/ui_ux_design.pdf', 0, 0),
(5, 'Python untuk Sains Data', 'Eko Prasetyo', '', '0000', '', 'Pendidikan', 'Memanfaatkan kekuatan Python dan library seperti Pandas dan Matplotlib untuk melakukan analisis dan visualisasi data secara efektif.', 'Gambar/covers/1757564727_Pyton.png', 'ebooks/python_data_sains.pdf', 0, 0),
(10, 'Informatikaa', 'Tim Kemendikbudristek', 'Kemendikbudristek', '2021', '978-602-244-506-7', 'Pendidikan', 'Buku ajar resmi untuk siswa SMA yang mencakup materi Berpikir Komputasional (BK), Teknologi Informasi dan Komunikasi (TIK), Sistem Komputer (SK), Jaringan Komputer dan Internet (JKI), dan Analisis Data (AD).', 'Gambar/covers/1757562808_coverinformatika.png', 'ebooks/1753345280_Informatika-BS-KLS-X.pdf', 1, 1),
(100, 'Komik Petualangan Si Ucup', 'Andi Pratama', 'Manga Nusantara', '2019', '978-602-123-000-1', 'Komik', 'Komik kocak tentang Ucup yang tersesat di dunia paralel.', 'Gambar/Covers/1759309456_ucup.png', 'ebooks/ucup_petualangan.pdf', 0, 0),
(101, 'Romansa Kopi Senjaa', 'Dina Laras', 'Sweetline Books', '2021', '978-602-123-000-2', 'Romansa', 'Cerita ringan tentang dua orang barista yang jatuh cinta.', 'Gambar/Covers/1759309553_kopii.png', 'ebooks/romansa_kopi.pdf', 0, 0),
(102, 'Horor Malam Jumats', 'Bayu Saputra', 'Dark Moon', '2020', '978-602-123-000-3', 'Horor', 'Kumpulan kisah mistis malam jumat dari berbagai daerah.', 'Gambar/Covers/1759310359_jumat.png', 'ebooks/horor_malam_jumat.pdf', 0, 0),
(103, 'Fantasi Negeri Awann', 'Sari Dewi', 'Aurora Press', '2018', '978-602-123-000-4', 'Fantasi', 'Petualangan di negeri awan yang penuh makhluk ajaib.', 'Gambar/Covers/1759309356_awan.png', 'ebooks/fantasi_negeri_awan.pdf', 0, 0),
(104, 'Thriller Kota Gelap', 'Raka Nugraha', 'Noir Line', '2022', '978-602-123-000-5', 'Thriller', 'Kisah detektif yang harus memecahkan kasus konspirasi di kota penuh rahasia.', 'Gambar/Covers/1759309266_horor.png', 'ebooks/thriller_kota_gelap.pdf', 1, 1),
(105, 'Komedi Anak Kos', 'Teguh Wibowo', 'Santai Press', '2020', '978-602-123-000-6', 'Komedi', 'Kumpulan cerita kocak kehidupan anak kos dengan segala dramanya.', 'Gambar/Covers/1759310640_komedi anak kos.png', 'ebooks/komedi_anak_kos.pdf', 0, 0),
(106, 'Petualangan Robot Kecil', 'Lina Prameswari', 'Future Kidss', '2021', '978-602-123-000-7', 'Anak-Anak', 'Kisah seru robot kecil yang belajar arti persahabatan sambil menjelajah kota.', 'Gambar/Covers/1759310593_robot.png', 'ebooks/petualangan_robot.pdf', 0, 0),
(108, 'Dune: Sang Mesias Padang Pasir', 'Frank Herbert', 'Gramedia Pustaka Utama', '2021', '978-602-06-5263-4', 'Fiksi Ilmiah', 'Kisah epik tentang politik, agama, dan kekuasaan di planet padang pasir Arrakis. Paul Atreides harus menavigasi takdirnya sebagai pemimpin yang dinubuatkan sambil menghadapi konspirasi dari berbagai faksi galaksi.', 'Gambar/Covers/1759374833_unnamed (1).png', 'ebooks/dune_ebook.pdf', 0, 0),
(109, 'Pembunuhan di Orient Expresss', 'Agatha Christie', 'Gramedia Pustaka Utama', '2017', '978-602-03-3975-7', 'Misteri', 'Detektif legendaris Hercule Poirot terjebak dalam kereta yang tertimbun salju dengan seorang korban pembunuhan. Semua penumpang adalah tersangka, dan Poirot harus memecahkan kasusnya sebelum si pembunuh bertindak lagi.', 'Gambar/Covers/1759374840_unnamed.png', 'ebooks/orient_express_ebook.pdf', 4, 2);

-- --------------------------------------------------------

--
-- Struktur dari tabel `comment_dislikes`
--

CREATE TABLE `comment_dislikes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `comment_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(13, 6, 37),
(10, 7, 37);

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
(37, 109, 7, 'Admin', 'haiiiiiiiii', 0, '2025-10-07 04:56:17', 2, 0),
(39, 109, 6, 'EDU', 'hia', 0, '2025-10-07 06:11:31', 0, 0),
(40, 109, 6, 'EDU', 'oke', 0, '2025-10-07 06:35:38', 0, 0),
(42, 10, 7, 'Admin', 'yang menciptakan adalah edu', 41, '2025-10-13 05:25:36', 0, 0),
(43, 104, 7, 'Admin', 'hi', 0, '2025-10-14 01:42:10', 0, 0);

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
  `username` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `nim` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') NOT NULL DEFAULT 'user',
  `avatar_seed` varchar(255) DEFAULT NULL,
  `bergabung_sejak` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `nim`, `password`, `role`, `avatar_seed`, `bergabung_sejak`) VALUES
(2, 'Budi Santoso', 'nelsonmeylina@gmail.com', '3312301001', '$2y$10$Tx2duiM4Pz/fHHJI5aOMheNef1KrK8QnizeP/N0TJGjgY5n8ks.3.', 'user', '503a2402c2460e035c7cd3ce31f84f9307b7e51b6c6f60b1b66723bc0ea9f641', '2025-10-07 08:00:05'),
(4, 'admin_utama', 'admin2@gmail.com', NULL, '$2y$10$vauZmBosJvPFVtAgVxSyXON10lQ0V/MxqPD/MfbaFSUXC6VuOuODi', 'admin', '801b9544c2318d06763ece80301da521d819f90bb2f0cb8f007ac5a73b36911e', '2025-10-07 08:00:05'),
(5, 'Andi Wijaya', 'andi@gmail.com', '3312301003', '$2y$10$844riWtfKQ/yzlpzuiJFKuKsJTHqrMQfohhc07TmX0lcXqiTVobUe', 'user', 'dcb6d4c36d5bfdad726264f385e02de8c783f83a8c1ef173b6346f0dc6ee6963', '2025-10-07 08:00:05'),
(6, 'EDU', 'edumail@gmail.com', '123456789', '$2y$10$gFgC2P3JvLcAHzKc5ROcqewwtUAPFpPPOvE4Re5U36yoviDNdBDd.', 'user', '0dd477424fd82bf30f582fc7300000d94a6937b7bd82522f16f0763406f476b8', '2025-10-07 08:00:05'),
(7, 'Admin', 'admin@gmail.com', 'N/A', '$2y$10$JGkJRxwg6Jefi7deJRo.MOqT.qWKmIStFkvxAfaM3UUdf2sQwsCJa', 'admin', '31d04a00006967cb860616af853232491da85448e05a87c42d3d1ac5691eddef', '2025-10-07 08:00:05'),
(9, 'santi', 'santi@gmail.com', '1111', '$2y$10$YHU8jT0BNL752PAuJZg6l.hNeN1PFAIK8eCEo40BcNZbDQWdJWvQy', 'user', '48671e288b1591efe62619bb7871f40adb3a412e4f049a8f07d0f4997f39de7d', '2025-10-07 08:00:05');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `book_ratings`
--
ALTER TABLE `book_ratings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_book_rating` (`user_id`,`buku_id`),
  ADD KEY `buku_id` (`buku_id`);

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
  ADD UNIQUE KEY `user_comment_dislike` (`user_id`,`comment_id`),
  ADD KEY `comment_id` (`comment_id`);

--
-- Indeks untuk tabel `comment_likes`
--
ALTER TABLE `comment_likes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_comment_like` (`user_id`,`comment_id`),
  ADD KEY `comment_id` (`comment_id`);

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
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `nim` (`nim`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `book_ratings`
--
ALTER TABLE `book_ratings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `buku`
--
ALTER TABLE `buku`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=110;

--
-- AUTO_INCREMENT untuk tabel `comment_dislikes`
--
ALTER TABLE `comment_dislikes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `comment_likes`
--
ALTER TABLE `comment_likes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT untuk tabel `diskusi`
--
ALTER TABLE `diskusi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `book_ratings`
--
ALTER TABLE `book_ratings`
  ADD CONSTRAINT `book_ratings_ibfk_1` FOREIGN KEY (`buku_id`) REFERENCES `buku` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `book_ratings_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `comment_dislikes`
--
ALTER TABLE `comment_dislikes`
  ADD CONSTRAINT `comment_dislikes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `comment_dislikes_ibfk_2` FOREIGN KEY (`comment_id`) REFERENCES `diskusi` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `comment_likes`
--
ALTER TABLE `comment_likes`
  ADD CONSTRAINT `comment_likes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `comment_likes_ibfk_2` FOREIGN KEY (`comment_id`) REFERENCES `diskusi` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `diskusi`
--
ALTER TABLE `diskusi`
  ADD CONSTRAINT `diskusi_ibfk_1` FOREIGN KEY (`buku_id`) REFERENCES `buku` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `diskusi_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
