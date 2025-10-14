<?php
session_start();
include '../includes/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $buku_id = $_POST['buku_id'];
    $rating = $_POST['rating'];

    // Validasi rating
    if ($rating >= 1 && $rating <= 5) {
        $conn->begin_transaction();
        try {
            // Cek apakah user sudah pernah rating buku ini
            $stmt_check = $conn->prepare("SELECT id, rating FROM book_ratings WHERE user_id = ? AND buku_id = ?");
            $stmt_check->bind_param("ii", $user_id, $buku_id);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();

            if ($result_check->num_rows > 0) {
                // User sudah pernah rating, jadi kita UPDATE ratingnya
                $existing_rating = $result_check->fetch_assoc();
                $old_rating_value = $existing_rating['rating'];
                
                $stmt_update_rating = $conn->prepare("UPDATE book_ratings SET rating = ? WHERE id = ?");
                $stmt_update_rating->bind_param("ii", $rating, $existing_rating['id']);
                $stmt_update_rating->execute();
                $stmt_update_rating->close();

                // Update total_rating di tabel buku
                $stmt_update_buku = $conn->prepare("UPDATE buku SET total_rating = total_rating - ? + ? WHERE id = ?");
                $stmt_update_buku->bind_param("iii", $old_rating_value, $rating, $buku_id);
                $stmt_update_buku->execute();
                $stmt_update_buku->close();

            } else {
                // User belum pernah rating, jadi kita INSERT
                $stmt_insert = $conn->prepare("INSERT INTO book_ratings (user_id, buku_id, rating) VALUES (?, ?, ?)");
                $stmt_insert->bind_param("iii", $user_id, $buku_id, $rating);
                $stmt_insert->execute();
                $stmt_insert->close();

                // Update total_rating dan rating_count di tabel buku
                $stmt_update_buku = $conn->prepare("UPDATE buku SET total_rating = total_rating + ?, rating_count = rating_count + 1 WHERE id = ?");
                $stmt_update_buku->bind_param("ii", $rating, $buku_id);
                $stmt_update_buku->execute();
                $stmt_update_buku->close();
            }

            $conn->commit();
            header("Location: detail_buku.php?id=" . $buku_id);
            exit;

        } catch (mysqli_sql_exception $exception) {
            $conn->rollback();
            die("Error: Gagal memproses rating.");
        }
    }
}

// Redirect jika ada masalah
header("Location: dashboard.php");
exit;
?>