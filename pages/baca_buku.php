<?php
session_start();
// FITUR INI WAJIB LOGIN - TIDAK ADA PERUBAHAN
// Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

// Ambil path file dari URL
if (!isset($_GET['file'])) {
    die("Error: File tidak ditemukan.");
}

// Keamanan: Pastikan path file aman dan berada di dalam direktori 'ebooks'
$requested_file = basename($_GET['file']); // Hanya ambil nama file untuk mencegah directory traversal
$safe_path = '../ebooks/' . $requested_file;

if (!file_exists($safe_path)) {
    die("Error: File buku tidak dapat diakses atau tidak ditemukan.");
}

// Ambil judul buku dari URL untuk ditampilkan di title
$book_title = isset($_GET['title']) ? htmlspecialchars($_GET['title']) : 'Baca Buku';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $book_title ?></title>
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body, html { margin: 0; padding: 0; height: 100%; overflow: hidden; background-color: #343a40; }
        .pdf-viewer { width: 100%; height: calc(100vh - 56px); border: none; }
        .viewer-header {
            background-color: #212529;
            color: white;
            padding: 0.75rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
    </style>
</head>
<body>
    <div class="viewer-header">
        <h5 class="mb-0 text-white-50"><i class="bi bi-book-fill me-2"></i><?= $book_title ?></h5>
        <a href="javascript:history.back()" class="btn btn-outline-light"><i class="bi bi-x-lg me-2"></i>Tutup</a>
    </div>
    <iframe class="pdf-viewer" src="<?= htmlspecialchars($safe_path) ?>"></iframe>
</body>
</html>