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

    <script>
        (function() {
            const theme = localStorage.getItem('theme');
            if (theme === 'dark') {
                document.documentElement.classList.add('dark-mode');
            }
        })();
    </script>

    <style>
        body {
            background-color: #f0f2f5; /* Latar belakang abu-abu lembut */
            padding-top: 2rem;
            padding-bottom: 2rem;
            transition: background-color 0.3s ease;
        }
        .book-detail-card {
            background-color: #ffffff;
            border-radius: 1rem;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
            border: none;
            overflow: hidden; 
        }
        .book-cover-container {
            padding: 2.5rem;
            background: #e9ecef; 
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            transition: background 0.5s ease-in-out;
        }
        .book-cover-img {
            max-width: 100%;
            max-height: 450px;
            object-fit: contain;
            border-radius: 0.5rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            transition: transform 0.3s ease;
            crossorigin="anonymous"
        }
        .book-cover-img:hover {
            transform: scale(1.05);
        }
        .book-info-container {
            padding: 2.5rem;
            display: flex;
            flex-direction: column;
        }
        .book-title { font-weight: 700; color: #212529; }
        .author-name { font-size: 1.25rem; color: #6c757d; margin-top: -5px; }
        .section-title { font-weight: 600; margin-top: 1.5rem; margin-bottom: 1rem; color: #495057; border-bottom: 2px solid #0d6efd; padding-bottom: 0.5rem; display: inline-block; }
        .details-list { list-style: none; padding: 0; }
        .details-list li { display: flex; align-items: flex-start; margin-bottom: 0.85rem; font-size: 1rem; }
        .details-list .icon { color: #0d6efd; margin-right: 12px; font-size: 1.2rem; width: 24px; text-align: center; padding-top: 2px; }
        .details-list .label { font-weight: 600; width: 110px; flex-shrink: 0; color: #343a40; }
        .details-list .value { color: #495057; }
        .action-buttons { margin-top: auto; padding-top: 1.5rem; }

        /* --- CSS BARU UNTUK DARK MODE --- */
        html.dark-mode body { background-color: #18191a; }
        html.dark-mode .book-detail-card { background-color: #242526; border-color: #3a3b3c; }
        html.dark-mode .book-title,
        html.dark-mode .section-title,
        html.dark-mode .details-list .label { color: #e4e6eb; }
        html.dark-mode .author-name,
        html.dark-mode p.text-secondary,
        html.dark-mode .details-list .value { color: #b0b3b8; }
        html.dark-mode .btn-outline-secondary { color: #e4e6eb; border-color: #6c757d; }
        html.dark-mode .btn-outline-secondary:hover { background-color: #6c757d; color: white; }
    </style>
</head>
<body>

<div class="container my-auto">
    <div class="book-detail-card">
        <div class="row g-0">
            <div class="col-lg-5 book-cover-container">
                <img src="../<?= htmlspecialchars($book['cover_path']) ?>" class="book-cover-img" alt="Cover <?= htmlspecialchars($book['judul']) ?>">
            </div>

            <div class="col-lg-7 book-info-container">
                <div>
                    <h1 class="book-title display-5"><?= htmlspecialchars($book['judul']) ?></h1>
                    <p class="author-name">oleh <?= htmlspecialchars($book['penulis']) ?></p>
                    
                    <h5 class="section-title">Deskripsi</h5>
                    <p class="text-secondary"><?= nl2br(htmlspecialchars($book['deskripsi'])) ?></p>

                    <h5 class="section-title">Detail Informasi</h5>
                    <ul class="details-list">
                        <li>
                            <i class="bi bi-building icon"></i>
                            <span class="label">Penerbit:</span>
                            <span class="value"><?= htmlspecialchars($book['penerbit'] ?? 'N/A') ?></span>
                        </li>
                        <li>
                            <i class="bi bi-calendar-event icon"></i>
                            <span class="label">Tahun Terbit:</span>
                            <span class="value"><?= htmlspecialchars($book['tahun_terbit'] ?? 'N/A') ?></span>
                        </li>
                        <li>
                            <i class="bi bi-hash icon"></i>
                            <span class="label">ISBN:</span>
                            <span class="value"><?= htmlspecialchars($book['isbn'] ?? 'N/A') ?></span>
                        </li>
                        <li>
                            <i class="bi bi-tag-fill icon"></i>
                            <span class="label">Kategori:</span>
                            <span class="value"><span class="badge bg-primary rounded-pill fs-6"><?= htmlspecialchars($book['kategori'] ?? 'Umum') ?></span></span>
                        </li>
                    </ul>
                </div>
                
                <div class="action-buttons">
                    <div class="d-grid gap-2">
                        <a href="baca_buku.php?file=<?= urlencode(basename($book['file_path'])) ?>&title=<?= urlencode($book['judul']) ?>" class="btn btn-success btn-lg">
                            <i class="bi bi-eye-fill"></i> Baca Buku Sekarang
                        </a>
                        <a href="diskusi.php?id=<?= $book['id'] ?>" class="btn btn-primary btn-lg">
                            <i class="bi bi-chat-dots-fill"></i> Lihat & Gabung Diskusi
                        </a>
                        <a href="dashboard.php" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left-circle"></i> Kembali ke Daftar Buku
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/color-thief/2.3.0/color-thief.umd.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', (event) => {
        const colorThief = new ColorThief();
        const img = document.querySelector('.book-cover-img');
        const targetContainer = document.querySelector('.book-cover-container');

        const setBackgroundColor = (imageElement) => {
            try {
                const dominantColor = colorThief.getColor(imageElement);
                const darkerColor = dominantColor.map(c => Math.max(0, c - 40));
                targetContainer.style.background = `linear-gradient(135deg, rgb(${dominantColor.join(',')}), rgb(${darkerColor.join(',')}))`;
            } catch (e) {
                console.error("Error getting color from image:", e);
            }
        };

        if (img.complete) {
            setBackgroundColor(img);
        } else {
            img.addEventListener('load', function() {
                setBackgroundColor(this);
            });
        }
    });
</script>
</body>
</html>