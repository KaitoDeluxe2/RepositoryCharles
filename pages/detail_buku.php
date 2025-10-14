<?php
session_start();
include '../includes/db.php';

$is_logged_in = isset($_SESSION['user_id']);

if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit;
}

$book_id = $_GET['id'];

// --- Mengambil data buku beserta rata-rata rating ---
$stmt = $conn->prepare("SELECT *, (total_rating / IF(rating_count = 0, 1, rating_count)) as avg_rating FROM buku WHERE id = ?");
$stmt->bind_param("i", $book_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: dashboard.php");
    exit;
}

$book = $result->fetch_assoc();
$stmt->close();

// --- Mengambil rating yang sudah diberikan oleh pengguna (jika ada) ---
$user_rating = 0;
if($is_logged_in) {
    $stmt_user_rating = $conn->prepare("SELECT rating FROM book_ratings WHERE buku_id = ? AND user_id = ?");
    $stmt_user_rating->bind_param("ii", $book_id, $_SESSION['user_id']);
    $stmt_user_rating->execute();
    $user_rating_result = $stmt_user_rating->get_result();
    if($user_rating_result->num_rows > 0) {
        $user_rating = $user_rating_result->fetch_assoc()['rating'];
    }
    $stmt_user_rating->close();
}

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
            background-color: #f0f2f5;
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

        .login-required-alert {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            border: none;
            box-shadow: 0 4px 15px rgba(245, 87, 108, 0.4);
        }
        .login-required-alert a {
            color: white;
            text-decoration: underline;
            font-weight: bold;
        }

        /* --- , sistem akan mengumpulkan semua rating yang masuk, menjumlahkannya, 
        lalu membaginya dengan jumlah orang yang memberi rating untuk mendapatkan nilai 
        rata-rata.  --- */
        .rating-display { font-size: 1.2rem; }
        .rating-stars { color: #ffc107; }
        .rating-form .star-rating { display: flex; flex-direction: row-reverse; justify-content: flex-end; }
        .rating-form .star-rating input[type="radio"] { display: none; }
        .rating-form .star-rating label { font-size: 2rem; color: #ced4da; cursor: pointer; transition: color 0.2s; }
        .rating-form .star-rating input[type="radio"]:checked ~ label,
        .rating-form .star-rating label:hover,
        .rating-form .star-rating label:hover ~ label { color: #ffc107; }

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
        html.dark-mode .rating-form .star-rating label { color: #495057; }
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
                    
                    <div class="d-flex align-items-center mb-3 rating-display">
                        <div class="rating-stars me-2">
                            <?php 
                            $avg_rating_rounded = round($book['avg_rating']);
                            for($i = 0; $i < 5; $i++):
                                echo $i < $avg_rating_rounded ? '<i class="bi bi-star-fill"></i>' : '<i class="bi bi-star"></i>';
                            endfor;
                            ?>
                        </div>
                        <span class="fw-bold me-2"><?= number_format($book['avg_rating'], 1) ?></span>
                        <span class="text-muted">(<?= $book['rating_count'] ?> rating)</span>
                    </div>

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

                    <?php if ($is_logged_in): ?>
                    <div class="rating-form mt-4">
                        <h5 class="section-title">Beri Rating Anda</h5>
                        <form id="ratingForm" action="rate_handler.php" method="POST">
                            <input type="hidden" name="buku_id" value="<?= $book_id ?>">
                            <div class="star-rating">
                                <input type="radio" id="star5" name="rating" value="5" <?= $user_rating == 5 ? 'checked' : '' ?> onchange="this.form.submit()" /><label for="star5" title="5 stars"><i class="bi bi-star-fill"></i></label>
                                <input type="radio" id="star4" name="rating" value="4" <?= $user_rating == 4 ? 'checked' : '' ?> onchange="this.form.submit()" /><label for="star4" title="4 stars"><i class="bi bi-star-fill"></i></label>
                                <input type="radio" id="star3" name="rating" value="3" <?= $user_rating == 3 ? 'checked' : '' ?> onchange="this.form.submit()" /><label for="star3" title="3 stars"><i class="bi bi-star-fill"></i></label>
                                <input type="radio" id="star2" name="rating" value="2" <?= $user_rating == 2 ? 'checked' : '' ?> onchange="this.form.submit()" /><label for="star2" title="2 stars"><i class="bi bi-star-fill"></i></label>
                                <input type="radio" id="star1" name="rating" value="1" <?= $user_rating == 1 ? 'checked' : '' ?> onchange="this.form.submit()" /><label for="star1" title="1 star"><i class="bi bi-star-fill"></i></label>
                            </div>
                        </form>
                    </div>
                    <?php endif; ?>
                    </div>
                
                <div class="action-buttons">
                    <?php if ($is_logged_in): ?>
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
                    <?php else: ?>
                    <div class="d-grid gap-2">
                        <a href="../login.php" class="btn btn-success btn-lg">
                            <i class="bi bi-box-arrow-in-right"></i> Login untuk Baca & Rating
                        </a>
                        <a href="../login.php" class="btn btn-primary btn-lg">
                            <i class="bi bi-chat-dots-fill"></i> Login untuk Diskusi
                        </a>
                        <a href="dashboard.php" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left-circle"></i> Kembali ke Daftar Buku
                        </a>
                    </div>
                    <?php endif; ?>
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