<?php
session_start();
include '../includes/db.php';

// Jika tidak ada ID buku di URL atau user belum login, kembalikan ke dashboard
if (!isset($_GET['id']) || !isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$book_id = $_GET['id'];
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Ambil data buku
$stmt_buku = $conn->prepare("SELECT judul, penulis, cover_path FROM buku WHERE id = ?");
$stmt_buku->bind_param("i", $book_id);
$stmt_buku->execute();
$result_buku = $stmt_buku->get_result();
if ($result_buku->num_rows === 0) {
    header("Location: dashboard.php"); // Buku tidak ditemukan
    exit;
}
$book = $result_buku->fetch_assoc();
$stmt_buku->close();

// Ambil semua data diskusi untuk buku ini
$stmt_diskusi = $conn->prepare("SELECT username, komentar, tanggal FROM diskusi WHERE buku_id = ? ORDER BY tanggal DESC");
$stmt_diskusi->bind_param("i", $book_id);
$stmt_diskusi->execute();
$result_diskusi = $stmt_diskusi->get_result();

$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diskusi: <?= htmlspecialchars($book['judul']) ?></title>
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        .comment-box { border-left: 3px solid #eee; }
    </style>
</head>
<body class="bg-light">

<div class="container my-5">
    <div class="col-lg-9 mx-auto">
        <div class="bg-white p-4 rounded-3 shadow-sm mb-4">
            <div class="row g-3 align-items-center">
                <div class="col-auto">
                    <img src="../<?= htmlspecialchars($book['cover_path']) ?>" class="rounded" alt="Cover" style="width: 80px;">
                </div>
                <div class="col">
                    <h1 class="h3 fw-bold mb-0">Diskusi untuk Buku:</h1>
                    <p class="fs-5 text-muted"><?= htmlspecialchars($book['judul']) ?></p>
                </div>
                 <div class="col-auto">
                    <a href="detail_buku.php?id=<?= $book_id; ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h5 class="card-title">Tulis Komentar Anda</h5>
                <form action="tambah_komentar.php" method="POST">
                    <input type="hidden" name="buku_id" value="<?= $book_id ?>">
                    <input type="hidden" name="user_id" value="<?= $user_id ?>">
                    <input type="hidden" name="username" value="<?= htmlspecialchars($username) ?>">
                    <div class="mb-3">
                        <textarea name="komentar" class="form-control" rows="3" placeholder="Apa pendapatmu tentang buku ini?" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Kirim Komentar</button>
                </form>
            </div>
        </div>

        <div class="bg-white p-4 rounded-3 shadow-sm">
            <h4 class="mb-4">Diskusi Terbaru</h4>
            <?php if ($result_diskusi->num_rows > 0): ?>
                <?php while($komentar = $result_diskusi->fetch_assoc()): ?>
                <div class="d-flex mb-4">
                    <div class="flex-shrink-0 me-3">
                        <i class="bi bi-person-circle fs-2 text-secondary"></i>
                    </div>
                    <div class="w-100">
                        <div class="d-flex justify-content-between">
                            <h6 class="fw-bold"><?= htmlspecialchars($komentar['username']) ?></h6>
                            <small class="text-muted"><?= date('d M Y, H:i', strtotime($komentar['tanggal'])) ?></small>
                        </div>
                        <p class="mb-0 comment-box ps-3 pt-1 pb-1"><?= nl2br(htmlspecialchars($komentar['komentar'])) ?></p>
                    </div>
                </div>
                <hr>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-center text-muted">Belum ada diskusi untuk buku ini. Jadilah yang pertama!</p>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>