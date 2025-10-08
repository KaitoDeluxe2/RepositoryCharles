<?php
session_start();
include '../includes/db.php';

header('Content-Type: application/json');

// Keamanan: Pastikan metode POST dan user login
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['user_id']) && isset($_POST['comment_id'])) {
    
    $comment_id = (int)$_POST['comment_id'];
    $user_id = $_SESSION['user_id'];

    // Verifikasi kepemilikan
    $stmt_check = $conn->prepare("SELECT user_id FROM diskusi WHERE id = ?");
    $stmt_check->bind_param("i", $comment_id);
    $stmt_check->execute();
    $result = $stmt_check->get_result();

    if ($result->num_rows > 0) {
        $comment = $result->fetch_assoc();
        
        // Izinkan hapus jika user_id cocok ATAU jika yang menghapus adalah admin
        if ($comment['user_id'] == $user_id || (isset($_SESSION['role']) && $_SESSION['role'] === 'admin')) {
            $stmt_delete = $conn->prepare("DELETE FROM diskusi WHERE id = ?");
            $stmt_delete->bind_param("i", $comment_id);
            if ($stmt_delete->execute()) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Gagal menghapus dari database.']);
            }
            $stmt_delete->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'Anda tidak memiliki izin untuk menghapus komentar ini.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Komentar tidak ditemukan.']);
    }
    $stmt_check->close();

} else {
    echo json_encode(['success' => false, 'message' => 'Akses tidak sah.']);
}

$conn->close();
?>