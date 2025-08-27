<?php
session_start();
include '../includes/db.php';

// Keamanan: Hanya proses jika metode POST dan user sudah login
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['user_id'])) {

    // Validasi input
    if (isset($_POST['buku_id'], $_POST['user_id'], $_POST['username'], $_POST['komentar']) && !empty(trim($_POST['komentar']))) {
        
        $buku_id = $_POST['buku_id'];
        $user_id = $_POST['user_id'];
        $username = $_POST['username'];
        $komentar = $_POST['komentar'];

        // Pastikan user_id dari form sama dengan dari session untuk keamanan
        if ($user_id != $_SESSION['user_id']) {
            die("Error: Autentikasi tidak valid.");
        }

        // Simpan ke database menggunakan prepared statement
        $stmt = $conn->prepare("INSERT INTO diskusi (buku_id, user_id, username, komentar) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiss", $buku_id, $user_id, $username, $komentar);

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
        // Kembali ke halaman sebelumnya jika memungkinkan
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