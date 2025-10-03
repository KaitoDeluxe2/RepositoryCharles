<?php
session_start();
include '../includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_POST['comment_id'])) {
    echo json_encode(['success' => false, 'message' => 'Akses ditolak.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$comment_id = (int)$_POST['comment_id'];

// Mulai transaksi untuk memastikan konsistensi data
$conn->begin_transaction();

try {
    // Cek apakah user sudah pernah like komentar ini
    $stmt_check = $conn->prepare("SELECT id FROM comment_likes WHERE user_id = ? AND comment_id = ?");
    $stmt_check->bind_param("ii", $user_id, $comment_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        // Jika sudah, maka ini adalah unlike
        // 1. Hapus dari tabel comment_likes
        $stmt_unlike = $conn->prepare("DELETE FROM comment_likes WHERE user_id = ? AND comment_id = ?");
        $stmt_unlike->bind_param("ii", $user_id, $comment_id);
        $stmt_unlike->execute();
        $stmt_unlike->close();

        // 2. Kurangi jumlah like di tabel diskusi
        $stmt_update = $conn->prepare("UPDATE diskusi SET likes = likes - 1 WHERE id = ?");
        $stmt_update->bind_param("i", $comment_id);
        $stmt_update->execute();
        $stmt_update->close();
        
        $liked = false;

    } else {
        // Jika belum, ini adalah like
        // 1. Tambahkan ke tabel comment_likes
        $stmt_like = $conn->prepare("INSERT INTO comment_likes (user_id, comment_id) VALUES (?, ?)");
        $stmt_like->bind_param("ii", $user_id, $comment_id);
        $stmt_like->execute();
        $stmt_like->close();

        // 2. Tambah jumlah like di tabel diskusi
        $stmt_update = $conn->prepare("UPDATE diskusi SET likes = likes + 1 WHERE id = ?");
        $stmt_update->bind_param("i", $comment_id);
        $stmt_update->execute();
        $stmt_update->close();

        $liked = true;
    }
    
    $stmt_check->close();

    // Ambil jumlah like terbaru
    $stmt_count = $conn->prepare("SELECT likes FROM diskusi WHERE id = ?");
    $stmt_count->bind_param("i", $comment_id);
    $stmt_count->execute();
    $new_like_count = $stmt_count->get_result()->fetch_assoc()['likes'];
    $stmt_count->close();

    // Jika semua berhasil, commit transaksi
    $conn->commit();

    echo json_encode(['success' => true, 'new_like_count' => $new_like_count, 'liked' => $liked]);

} catch (mysqli_sql_exception $exception) {
    $conn->rollback(); // Batalkan semua query jika ada error
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $exception->getMessage()]);
}

$conn->close();
?>