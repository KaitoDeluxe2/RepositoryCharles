<?php
session_start();
include '../includes/db.php';

// Jika tidak ada ID buku di URL, kembalikan ke dashboard
if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit;
}

$book_id = $_GET['id'];

// Ambil semua data untuk buku yang dipilih
$stmt = $conn->prepare("SELECT * FROM buku WHERE id = ?");
$stmt->bind_param("i", $book_id);
$stmt->execute();
$result = $stmt->get_result();

// Jika buku tidak ditemukan, kembalikan ke dashboard
if ($result->num_rows === 0) {
    header("Location: dashboard.php");
    exit;
}

$book = $result->fetch_assoc();
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Buku - <?= htmlspecialchars($book['judul']) ?></title>
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        .book-details dt {
            font-weight: 600;
            color: #555;
        }
    </style>
</head>
<body class="bg-light">

<div class="container my-5">
    <div class="bg-white p-4 p-md-5 rounded-3 shadow-sm">
        <div class="row g-4">
            <div class="col-md-4 text-center">
                <img src="<?= htmlspecialchars($book['cover_path']) ?>" class="img-fluid rounded shadow" alt="Cover <?= htmlspecialchars($book['judul']) ?>" style="max-height: 400px;">
            </div>

            <div class="col-md-8">
                <h1 class="h2 fw-bold"><?= htmlspecialchars($book['judul']) ?></h1>
                <p class="fs-5 text-muted">oleh <?= htmlspecialchars($book['penulis']) ?></p>
                <hr>
                
                <h5 class="mt-4">Deskripsi</h5>
                <p><?= nl2br(htmlspecialchars($book['deskripsi'])) ?></p>

                <h5 class="mt-4">Detail Informasi</h5>
                <dl class="row book-details">
                    <dt class="col-sm-3">Penerbit</dt>
                    <dd class="col-sm-9"><?= htmlspecialchars($book['penerbit'] ?? 'N/A') ?></dd>

                    <dt class="col-sm-3">Tahun Terbit</dt>
                    <dd class="col-sm-9"><?= htmlspecialchars($book['tahun_terbit'] ?? 'N/A') ?></dd>

                    <dt class="col-sm-3">ISBN</dt>
                    <dd class="col-sm-9"><?= htmlspecialchars($book['isbn'] ?? 'N/A') ?></dd>
                    
                    <dt class="col-sm-3">Kategori</dt>
                    <dd class="col-sm-9"><span class="badge bg-secondary"><?= htmlspecialchars($book['kategori'] ?? 'Umum') ?></span></dd>
                </dl>
                
                <div class="d-grid gap-2 mt-4">
                    <a href="diskusi.php?id=<?= $book['id'] ?>" class="btn btn-primary btn-lg">
    <i class="bi bi-chat-dots-fill"></i> Lihat Diskusi
</a>
                    <a href="dashboard.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Kembali ke Daftar Buku
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>