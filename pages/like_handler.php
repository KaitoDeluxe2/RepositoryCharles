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

// Mulai transaksi untuk memastikan semua query berhasil atau gagal bersamaan
$conn->begin_transaction();

try {
    // Cek apakah user sudah pernah like komentar ini
    $stmt_check_like = $conn->prepare("SELECT id FROM comment_likes WHERE user_id = ? AND comment_id = ?");
    $stmt_check_like->bind_param("ii", $user_id, $comment_id);
    $stmt_check_like->execute();
    $is_liked = $stmt_check_like->get_result()->num_rows > 0;
    $stmt_check_like->close();

    $liked_status = false;

    if ($is_liked) {
        // --- PROSES UNLIKE ---
        // 1. Hapus dari tabel comment_likes
        $stmt_unlike = $conn->prepare("DELETE FROM comment_likes WHERE user_id = ? AND comment_id = ?");
        $stmt_unlike->bind_param("ii", $user_id, $comment_id);
        $stmt_unlike->execute();
        $stmt_unlike->close();

        // 2. Kurangi jumlah likes di tabel diskusi
        $stmt_update = $conn->prepare("UPDATE diskusi SET likes = likes - 1 WHERE id = ? AND likes > 0");
        $stmt_update->bind_param("i", $comment_id);
        $stmt_update->execute();
        $stmt_update->close();
        
        $liked_status = false;

    } else {
        // --- PROSES LIKE BARU ---
        // 1. Hapus dislike yang mungkin ada sebelumnya
        $stmt_check_dislike = $conn->prepare("SELECT id FROM comment_dislikes WHERE user_id = ? AND comment_id = ?");
        $stmt_check_dislike->bind_param("ii", $user_id, $comment_id);
        $stmt_check_dislike->execute();
        if ($stmt_check_dislike->get_result()->num_rows > 0) {
            $stmt_undislike = $conn->prepare("DELETE FROM comment_dislikes WHERE user_id = ? AND comment_id = ?");
            $stmt_undislike->bind_param("ii", $user_id, $comment_id);
            $stmt_undislike->execute();
            $stmt_undislike->close();
            
            $stmt_update_dislikes = $conn->prepare("UPDATE diskusi SET dislikes = dislikes - 1 WHERE id = ? AND dislikes > 0");
            $stmt_update_dislikes->bind_param("i", $comment_id);
            $stmt_update_dislikes->execute();
            $stmt_update_dislikes->close();
        }
        $stmt_check_dislike->close();

        // 2. Tambahkan like baru ke tabel comment_likes
        $stmt_like = $conn->prepare("INSERT INTO comment_likes (user_id, comment_id) VALUES (?, ?)");
        $stmt_like->bind_param("ii", $user_id, $comment_id);
        $stmt_like->execute();
        $stmt_like->close();

        // 3. Tambah jumlah likes di tabel diskusi
        $stmt_update_likes = $conn->prepare("UPDATE diskusi SET likes = likes + 1 WHERE id = ?");
        $stmt_update_likes->bind_param("i", $comment_id);
        $stmt_update_likes->execute();
        $stmt_update_likes->close();

        $liked_status = true;
    }

    // Ambil jumlah like & dislike terbaru untuk dikirim kembali ke frontend
    $stmt_count = $conn->prepare("SELECT likes, dislikes FROM diskusi WHERE id = ?");
    $stmt_count->bind_param("i", $comment_id);
    $stmt_count->execute();
    $counts = $stmt_count->get_result()->fetch_assoc();
    $stmt_count->close();
    
    // Jika semua berhasil, commit transaksi
    $conn->commit();

    echo json_encode([
        'success' => true, 
        'new_like_count' => $counts['likes'],
        'new_dislike_count' => $counts['dislikes'], 
        'liked' => $liked_status
    ]);

} catch (mysqli_sql_exception $exception) {
    $conn->rollback(); // Batalkan semua query jika ada error
    echo json_encode(['success' => false, 'message' => 'Database error.']);
}

$conn->close();
?>