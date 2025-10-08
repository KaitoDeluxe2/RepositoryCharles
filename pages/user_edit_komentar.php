<?php
session_start();
include '../includes/db.php';

// Keamanan: Pastikan metode POST, user login, dan semua data ada
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['user_id']) && isset($_POST['comment_id'], $_POST['komentar'])) {

    $comment_id = (int)$_POST['comment_id'];
    $user_id = $_SESSION['user_id'];
    $new_komentar = trim($_POST['komentar']);
    $buku_id = $_POST['buku_id']; // Untuk redirect kembali

    if (empty($new_komentar)) {
        // Redirect jika komentar kosong setelah diedit
        header("Location: diskusi.php?id=" . $buku_id . "&status=edit_gagal");
        exit;
    }

    // Verifikasi kepemilikan: Cek apakah user yang sedang login adalah pemilik komentar
    $stmt_check = $conn->prepare("SELECT user_id FROM diskusi WHERE id = ?");
    $stmt_check->bind_param("i", $comment_id);
    $stmt_check->execute();
    $result = $stmt_check->get_result();

    if ($result->num_rows > 0) {
        $comment = $result->fetch_assoc();
        
        // Hanya izinkan update jika user_id cocok
        if ($comment['user_id'] == $user_id) {
            $stmt_update = $conn->prepare("UPDATE diskusi SET komentar = ? WHERE id = ?");
            $stmt_update->bind_param("si", $new_komentar, $comment_id);
            $stmt_update->execute();
            $stmt_update->close();
        }
    }
    $stmt_check->close();

    // Redirect kembali ke halaman diskusi
    header("Location: diskusi.php?id=" . $buku_id);
    exit;

} else {
    // Jika akses tidak sah, redirect ke dashboard
    header("Location: dashboard.php");
    exit;
}

$conn->close();
?>