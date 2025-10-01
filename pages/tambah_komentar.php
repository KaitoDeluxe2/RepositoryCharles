<?php
session_start();
include '../includes/db.php';

// Keamanan: Hanya proses jika metode POST dan user sudah login
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['user_id'])) {

    // Validasi input
    if (isset($_POST['buku_id'], $_POST['komentar']) && !empty(trim($_POST['komentar']))) {
        
        $buku_id = $_POST['buku_id'];
        $user_id = $_SESSION['user_id'];
        $username = $_SESSION['username'];
        $komentar = $_POST['komentar'];
        // Ambil parent_id dari form, defaultnya 0 jika tidak ada
        $parent_id = isset($_POST['parent_id']) ? intval($_POST['parent_id']) : 0;

        // Simpan ke database menggunakan prepared statement
        $stmt = $conn->prepare("INSERT INTO diskusi (buku_id, user_id, username, komentar, parent_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iisss", $buku_id, $user_id, $username, $komentar, $parent_id);

        if ($stmt->execute()) {
            // Jika berhasil, kembalikan ke halaman diskusi
            header("Location: diskusi.php?id=" . $buku_id);
            exit;
        } else {
            // Jika gagal
            die("Error: Gagal menyimpan komentar.");
        }

        $stmt->close();
    } else {
        // Jika ada data yang kosong
        $buku_id = $_POST['buku_id'] ?? 'dashboard.php';
        header("Location: diskusi.php?id=" . $buku_id);
        exit;
    }

} else {
    // Jika diakses secara langsung, redirect ke dashboard
    header("Location: dashboard.php");
    exit;
}

$conn->close();
?>