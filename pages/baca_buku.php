<?php
session_start();
// Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$file_path = 'ebooks/default.pdf'; // File default jika tidak ada file yang diberikan
if (isset($_GET['file'])) {
    // KEAMANAN: Pastikan path file aman dan berada di dalam direktori 'ebooks'
    $requested_file = basename($_GET['file']); // Hanya ambil nama file untuk keamanan
    $safe_path = '../ebooks/' . $requested_file;

    if (file_exists($safe_path)) {
        $file_path = $safe_path;
    } else {
        die("Error: File buku tidak ditemukan.");
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Baca Buku</title>
    <style>
        body, html { margin: 0; padding: 0; height: 100%; overflow: hidden; }
        .pdf-viewer { width: 100%; height: 100vh; border: none; }
    </style>
</head>
<body>
    <iframe class="pdf-viewer" src="<?= htmlspecialchars($file_path) ?>"></iframe>
</body>
</html>