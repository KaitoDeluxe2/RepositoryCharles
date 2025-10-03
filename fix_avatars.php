<?php
/**
 * fix_avatars.php (Versi 2 - Lebih Canggih)
 */

include 'includes/db.php';

// Header HTML untuk tampilan yang lebih baik
echo "<!DOCTYPE html>
<html lang='id'>
<head>
    <meta charset='UTF-8'>
    <title>Proses Perbaikan Avatar Pengguna</title>
    <link href='css/bootstrap.min.css' rel='stylesheet'>
    <style>
        body { font-family: sans-serif; background-color: #f8f9fa; }
        .container { max-width: 800px; margin-top: 2rem; }
        .card { padding: 2rem; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        .log {
            background-color: #e9ecef;
            border-left: 5px solid #6c757d;
            padding: 1rem;
            margin-top: 1.5rem;
            max-height: 400px;
            overflow-y: auto;
            font-family: monospace;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
<div class='container'>
    <div class='card text-center'>
        <h1 class='mb-3'>Memulai Proses Perbaikan Avatar</h1>";

// [DIUBAH] Query sekarang mencari pengguna yang avatar_seed-nya NULL ATAU string kosong
$result = $conn->query("SELECT id, username FROM users WHERE avatar_seed IS NULL OR avatar_seed = ''");

if ($result && $result->num_rows > 0) {
    echo "<p class='lead'>Ditemukan " . $result->num_rows . " pengguna tanpa avatar. Memproses...</p>";
    echo "<div class='log text-start'>";
    
    $stmt = $conn->prepare("UPDATE users SET avatar_seed = ? WHERE id = ?");

    $updated_count = 0;
    while ($user = $result->fetch_assoc()) {
        $user_id = $user['id'];
        $username = $user['username'];
        
        $new_seed = hash('sha256', $username . $user_id . time() . rand());
        
        $stmt->bind_param("si", $new_seed, $user_id);
        if ($stmt->execute()) {
            echo "SUCCESS: Pengguna '<strong>" . htmlspecialchars($username) . "</strong>' (ID: " . $user_id . ") berhasil diperbarui.<br>";
            $updated_count++;
        } else {
            echo "ERROR: Gagal memperbarui pengguna '" . htmlspecialchars($username) . "'.<br>";
        }
    }
    
    $stmt->close();
    echo "</div>";
    echo "<h3 class='mt-4'>Proses Selesai!</h3>";
    echo "<p class='alert alert-success'><strong>" . $updated_count . "</strong> data pengguna telah berhasil diperbaiki.</p>";

} else {
    echo "<h3 class='mt-4'>Tidak ada yang perlu diperbaiki.</h3>";
    echo "<p class='alert alert-info'>Semua pengguna di database Anda sudah memiliki avatar.</p>";
}

echo '<a href="index.php" class="btn btn-primary mt-3">Kembali ke Halaman Utama</a>';

$conn->close();

echo "  </div>
    </div>
</body>
</html>";
?>