<?php
session_start();
include '../includes/db.php';

// Cek apakah user adalah admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php?error=unauthorized');
    exit;
}

// Ambil parameter dari URL
$comment_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$book_id = isset($_GET['book_id']) ? intval($_GET['book_id']) : 0;

// Validasi input
if ($comment_id <= 0 || $book_id <= 0) {
    header('Location: dashboard.php?error=invalid_data');
    exit;
}

// Cek apakah komentar ada di database
$stmt = $conn->prepare("SELECT id, username, komentar FROM diskusi WHERE id = ?");
$stmt->bind_param("i", $comment_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    $conn->close();
    header("Location: diskusi.php?id={$book_id}&error=comment_not_found");
    exit;
}

$comment_data = $result->fetch_assoc();
$stmt->close();

// Hapus komentar dari database
$stmt = $conn->prepare("DELETE FROM diskusi WHERE id = ?");
$stmt->bind_param("i", $comment_id);

if ($stmt->execute()) {
    $stmt->close();
    $conn->close();
    
    // Redirect dengan pesan sukses
    header("Location: diskusi.php?id={$book_id}&success=comment_deleted&deleted_user=" . urlencode($comment_data['username']));
    exit;
} else {
    $stmt->close();
    $conn->close();
    
    // Redirect dengan pesan error
    header("Location: diskusi.php?id={$book_id}&error=delete_failed");
    exit;
}
?>