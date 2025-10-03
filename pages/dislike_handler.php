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

$conn->begin_transaction();

try {
    // Cek apakah user sudah pernah dislike
    $stmt_check = $conn->prepare("SELECT id FROM comment_dislikes WHERE user_id = ? AND comment_id = ?");
    $stmt_check->bind_param("ii", $user_id, $comment_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        // Jika sudah, maka ini adalah undislike
        $stmt_unlike = $conn->prepare("DELETE FROM comment_dislikes WHERE user_id = ? AND comment_id = ?");
        $stmt_unlike->bind_param("ii", $user_id, $comment_id);
        $stmt_unlike->execute();
        $stmt_unlike->close();

        $stmt_update = $conn->prepare("UPDATE diskusi SET dislikes = dislikes - 1 WHERE id = ?");
        $stmt_update->bind_param("i", $comment_id);
        $stmt_update->execute();
        $stmt_update->close();
        
        $disliked = false;
    } else {
        // Jika belum, ini adalah dislike
        $stmt_like = $conn->prepare("INSERT INTO comment_dislikes (user_id, comment_id) VALUES (?, ?)");
        $stmt_like->bind_param("ii", $user_id, $comment_id);
        $stmt_like->execute();
        $stmt_like->close();

        $stmt_update = $conn->prepare("UPDATE diskusi SET dislikes = dislikes + 1 WHERE id = ?");
        $stmt_update->bind_param("i", $comment_id);
        $stmt_update->execute();
        $stmt_update->close();

        $disliked = true;
    }
    
    $stmt_check->close();

    // Ambil jumlah dislike terbaru
    $stmt_count = $conn->prepare("SELECT dislikes FROM diskusi WHERE id = ?");
    $stmt_count->bind_param("i", $comment_id);
    $stmt_count->execute();
    $new_dislike_count = $stmt_count->get_result()->fetch_assoc()['dislikes'];
    $stmt_count->close();
    
    $conn->commit();

    echo json_encode(['success' => true, 'new_dislike_count' => $new_dislike_count, 'disliked' => $disliked]);

} catch (mysqli_sql_exception $exception) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Database error.']);
}

$conn->close();
?>