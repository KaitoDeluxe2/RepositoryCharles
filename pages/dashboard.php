<?php
session_start();
include '../includes/db.php';

$is_logged_in = isset($_SESSION['user_id']);
$namaPengguna = $is_logged_in ? htmlspecialchars($_SESSION['username']) : 'Pengunjung';

// --- KODE BARU: Query untuk Buku Populer ---
$sql_popular = "
    SELECT 
        b.id, 
        b.judul, 
        b.cover_path, 
        b.penulis,
        (SELECT COUNT(*) FROM diskusi WHERE buku_id = b.id) AS jumlah_komentar,
        (b.total_rating / IF(b.rating_count = 0, 1, b.rating_count)) AS avg_rating
    FROM buku b
    ORDER BY avg_rating DESC, jumlah_komentar DESC
    LIMIT 6";

$popular_books_result = $conn->query($sql_popular);
// --- AKHIR KODE BARU ---

$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$is_searching = !empty($search_query);

$buku_per_halaman = 12;
$halaman_aktif = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($halaman_aktif < 1) {
    $halaman_aktif = 1;
}

$sql_data = "SELECT id, judul, cover_path, penulis FROM buku";
$sql_count = "SELECT COUNT(id) AS total FROM buku";

$params = [];
$types = "";

if ($is_searching) {
    $search_term = "%" . $search_query . "%";
    $where_clause = " WHERE judul LIKE ? OR penulis LIKE ?";
    $sql_data .= $where_clause;
    $sql_count .= $where_clause;
    $params[] = &$search_term;
    $params[] = &$search_term;
    $types .= "ss";
}

$stmt_count = $conn->prepare($sql_count);
if ($is_searching) {
    $stmt_count->bind_param($types, ...$params);
}
$stmt_count->execute();
$total_buku = $stmt_count->get_result()->fetch_assoc()['total'];
$stmt_count->close();

$total_halaman = ceil($total_buku / $buku_per_halaman);
if ($halaman_aktif > $total_halaman && $total_halaman > 0) {
    $halaman_aktif = $total_halaman;
}

$offset = ($halaman_aktif - 1) * $buku_per_halaman;

$sql_data .= " ORDER BY id DESC LIMIT ? OFFSET ?";
$params[] = &$buku_per_halaman;
$params[] = &$offset;
$types .= "ii";

$stmt_data = $conn->prepare($sql_data);
if (!empty($types)) {
    $stmt_data->bind_param($types, ...$params);
}
$stmt_data->execute();
$books_result = $stmt_data->get_result();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>DIGISPACE</title>
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
    body { background-color: #f8f9fa; }
    .navbar-brand { font-weight: bold; }
    .hero-section {
        position: relative; padding: 6rem 1rem; text-align: center; color: white;
        background: url('../Gambar/perpuss.png') center center / cover no-repeat;
    }
    .hero-section::before {
        content: ''; position: absolute; top: 0; left: 0; right: 0; bottom: 0;
        background-color: rgba(0, 0, 0, 0.5); 
    }
    .hero-section .container { position: relative; z-index: 2; }
    .search-form { background: rgba(255, 255, 255, 0.9); padding: 0.5rem; border-radius: 0.5rem; backdrop-filter: blur(5px); }
    .search-form .form-control { border: none; background: transparent; }
    .search-form .form-control:focus { box-shadow: none; }
    .search-form .btn { border-radius: 0.3rem; }
    .section-title { font-weight: 700; margin-bottom: 1.5rem; }
    .book-item .card { transition: transform .2s, box-shadow .2s; border: 1px solid #eee; }
    .book-item .card:hover { transform: translateY(-8px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.1); }
    .book-cover { height: 280px; object-fit: cover; width: 100%; }
    .book-title {
        margin-top: 0.75rem; font-weight: 600; color: #343a40;
        overflow: hidden; text-overflow: ellipsis; display: -webkit-box;
        -webkit-line-clamp: 2; -webkit-box-orient: vertical; min-height: 48px;
    }
    #animated-subtitle { transition: opacity 0.5s ease-in-out; }

    .theme-switch-wrapper { display: flex; align-items: center; cursor: pointer; }
    .theme-switch { display: inline-block; height: 24px; position: relative; width: 48px; }
    .theme-switch input { display:none; }
    .slider { background-color: #ccc; bottom: 0; cursor: pointer; left: 0; position: absolute; right: 0; top: 0; transition: .4s; }
    .slider:before { background-color: #fff; bottom: 4px; content: ""; height: 16px; left: 4px; position: absolute; transition: .4s; width: 16px; }
    input:checked + .slider { background-color: #0d6efd; }
    input:checked + .slider:before { transform: translateX(24px); }
    .slider.round { border-radius: 34px; }
    .slider.round:before { border-radius: 50%; }
    .theme-switch-wrapper .bi { margin-left: 8px; font-size: 1.2rem; color: #fff; }

    .guest-alert { 
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
    }
    .guest-alert a { color: #fff; text-decoration: underline; font-weight: bold; }

    .rating-stars { color: #ffc107; }

    html.dark-mode body { background-color: #18191a; color: #e4e6eb; }
    html.dark-mode .bg-white, html.dark-mode .card, html.dark-mode .bg-light, html.dark-mode .dropdown-menu { background-color: #242526 !important; color: #e4e6eb; border-color: #3a3b3c !important; }
    html.dark-mode .navbar-dark { background-color: rgba(33, 37, 41, 0.85) !important; backdrop-filter: blur(10px); }
    html.dark-mode h1, html.dark-mode h2, html.dark-mode h3, html.dark-mode h4, html.dark-mode h5, html.dark-mode h6, html.dark-mode .book-title a { color: #e4e6eb; }
    html.dark-mode .text-muted { color: #b0b3b8 !important; }
    html.dark-mode .btn-outline-secondary { color: #e4e6eb; border-color: #6c757d; }
    html.dark-mode .btn-outline-secondary:hover { background-color: #6c757d; color: white; }
    html.dark-mode .book-title { color: #e4e6eb; }
    html.dark-mode .pagination .page-link { background-color: #242526; border-color: #3a3b3c; color: #0d6efd; }
    html.dark-mode .pagination .page-item.disabled .page-link { background-color: #3a3b3c; color: #6c757d; }
    html.dark-mode .pagination .page-item.active .page-link { background-color: #0d6efd; border-color: #0d6efd; }
    html.dark-mode .dropdown-item { color: #e4e6eb; }
    html.dark-mode .dropdown-item:hover { background-color: #3a3b3c; }
    html.dark-mode .dropdown-divider { border-top-color: #3a3b3c; }
    html.dark-mode .search-form { background: rgba(36, 37, 38, 0.9); }
    html.dark-mode .search-form .form-control { color: #fff; }
    html.dark-mode .search-form .form-control::placeholder { color: #b0b3b8; }
  </style>
</head>
<body>
  
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <div class="container">
      <a class="navbar-brand" href="dashboard.php"><i class="bi bi-book-half"></i> DIGISPACE</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNavDropdown">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item me-3 d-flex align-items-center">
            <div class="theme-switch-wrapper">
                <label class="theme-switch" for="theme-checkbox">
                    <input type="checkbox" id="theme-checkbox" />
                    <div class="slider round"></div>
                </label>
                <i class="bi bi-moon-fill"></i>
            </div>
          </li>
          
          <?php if ($is_logged_in): ?>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" role="button" data-bs-toggle="dropdown"><i class="bi bi-person-circle"></i> <?= $namaPengguna ?></a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="akun.php">Profile Saya</a></li>          
              <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
              <li><a class="dropdown-item" href="../admin/">Admin Panel</a></li>
              <?php endif; ?>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item text-danger" href="../logout.php">Logout</a></li>
            </ul>
          </li>
          <?php else: ?>
          <li class="nav-item">
            <a class="nav-link btn btn-outline-light btn-sm me-2" href="../login.php">
              <i class="bi bi-box-arrow-in-right"></i> Login
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link btn btn-light btn-sm" href="../register.html">
              <i class="bi bi-person-plus-fill"></i> Register
            </a>
          </li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </nav>

  <header class="hero-section">
    <div class="container">
      <h1 class="display-5 fw-bold">DIGISPACE Politeknik Negeri Batam</h1>
      <p class="lead" id="animated-subtitle">Temukan sumber referensi untuk menunjang perkuliahan Anda.</p>
      <div class="col-lg-8 mx-auto mt-4">
        <form action="dashboard.php" method="GET" class="search-form">
          <div class="input-group">
            <input type="text" name="search" class="form-control form-control-lg" placeholder="Masukkan judul buku atau penulis..." value="<?= htmlspecialchars($search_query) ?>">
            <button class="btn btn-primary px-4" type="submit"><i class="bi bi-search"></i></button>
          </div>
        </form>
      </div>
    </div>
  </header>

  <main class="container my-5">
    
    <?php if (!$is_logged_in): ?>
    <div class="alert guest-alert alert-dismissible fade show" role="alert">
      <h5 class="alert-heading"><i class="bi bi-info-circle-fill"></i> Halo, Pengunjung!</h5>
      <p class="mb-0">Anda dapat melihat koleksi buku, tetapi untuk <strong>membaca buku lengkap</strong>, <strong>memberi rating</strong>, dan <strong>berdiskusi</strong>, silakan <a href="../login.php">login terlebih dahulu</a>.</p>
      <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>
    
    <?php if ($popular_books_result->num_rows > 0): ?>
    <section class="book-collection mb-5">
        <h2 class="section-title">Koleksi Populer</h2>
        <div class="row row-cols-2 row-cols-md-4 row-cols-lg-6 g-4">
            <?php while($book = $popular_books_result->fetch_assoc()): ?>
            <div class="col">
                <div class="book-item text-center">
                    <a href="detail_buku.php?id=<?= $book['id'] ?>">
                        <div class="card shadow-sm">
                            <img src="../<?= htmlspecialchars($book['cover_path']) ?>" class="book-cover" alt="Cover Buku">
                        </div>
                    </a>
                    <h6 class="book-title">
                        <a href="detail_buku.php?id=<?= $book['id'] ?>" class="text-decoration-none">
                            <?= htmlspecialchars($book['judul']) ?>
                        </a>
                    </h6>
                    <div class="rating-stars">
                        <?php
                        $rating = round($book['avg_rating']);
                        for ($i = 0; $i < 5; $i++):
                            echo $i < $rating ? '<i class="bi bi-star-fill"></i>' : '<i class="bi bi-star"></i>';
                        endfor;
                        ?>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </section>
    <?php endif; ?>
    <section class="book-collection">
      <?php if ($is_searching): ?>
        <h2 class="section-title">Hasil pencarian untuk: "<?= htmlspecialchars($search_query) ?>"</h2>
      <?php else: ?>
        <h2 class="section-title">Semua Koleksi</h2>
      <?php endif; ?>
      
      <div class="row row-cols-2 row-cols-md-4 row-cols-lg-6 g-4">
        
        <?php if ($books_result->num_rows > 0): ?>
          <?php while($book = $books_result->fetch_assoc()): ?>
          <div class="col">
            <div class="book-item text-center">
              <a href="detail_buku.php?id=<?= $book['id'] ?>">
                <div class="card shadow-sm">
                    <img src="../<?= htmlspecialchars($book['cover_path']) ?>" class="book-cover" alt="Cover Buku">
                </div>
              </a>
              <h6 class="book-title">
                <a href="detail_buku.php?id=<?= $book['id'] ?>" class="text-decoration-none">
                    <?= htmlspecialchars($book['judul']) ?>
                </a>
              </h6>
            </div>
          </div>
          <?php endwhile; ?>
        <?php else: ?>
          <div class="col-12 text-center py-5">
            <?php if ($is_searching): ?>
                <p class="text-muted fs-5">Tidak ada buku yang cocok dengan pencarian Anda.</p>
                <a href="dashboard.php" class="btn btn-primary mt-2">Lihat Semua Koleksi</a>
            <?php else: ?>
                <p class="text-muted fs-5">Koleksi buku masih kosong.</p>
            <?php endif; ?>
          </div>
        <?php endif; ?>
        </div>
    </section>

    <?php if ($total_halaman > 1): ?>
    <nav aria-label="Navigasi Halaman" class="mt-5 d-flex justify-content-center">
      <ul class="pagination shadow-sm">
        <li class="page-item <?= ($halaman_aktif <= 1) ? 'disabled' : '' ?>">
          <a class="page-link" href="?page=<?= $halaman_aktif - 1 ?><?= $is_searching ? '&search=' . urlencode($search_query) : '' ?>">Sebelumnya</a>
        </li>
        <?php for ($i = 1; $i <= $total_halaman; $i++): ?>
        <li class="page-item <?= ($i == $halaman_aktif) ? 'active' : '' ?>">
          <a class="page-link" href="?page=<?= $i ?><?= $is_searching ? '&search=' . urlencode($search_query) : '' ?>"><?= $i ?></a>
        </li>
        <?php endfor; ?>
        <li class="page-item <?= ($halaman_aktif >= $total_halaman) ? 'disabled' : '' ?>">
          <a class="page-link" href="?page=<?= $halaman_aktif + 1 ?><?= $is_searching ? '&search=' . urlencode($search_query) : '' ?>">Selanjutnya</a>
        </li>
      </ul>
    </nav>
    <?php endif; ?>
  </main>

  <footer class="text-center py-4 mt-5 bg-light border-top">
    <p class="mb-0">&copy; 2025 DIGISPACE Polibatam. All Rights Reserved.</p>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
        const themeCheckbox = document.getElementById('theme-checkbox');
        const themeIcon = document.querySelector('.theme-switch-wrapper .bi');

        function applyTheme(theme) {
            if (theme === 'dark') {
                document.documentElement.classList.add('dark-mode');
                if(themeCheckbox) themeCheckbox.checked = true;
                if(themeIcon) {
                    themeIcon.classList.remove('bi-moon-fill');
                    themeIcon.classList.add('bi-sun-fill');
                }
            } else {
                document.documentElement.classList.remove('dark-mode');
                if(themeCheckbox) themeCheckbox.checked = false;
                if(themeIcon) {
                    themeIcon.classList.remove('bi-sun-fill');
                    themeIcon.classList.add('bi-moon-fill');
                }
            }
        }

        const currentTheme = localStorage.getItem('theme') || 'light';
        applyTheme(currentTheme);

        if(themeCheckbox) {
            themeCheckbox.addEventListener('change', function() {
                const newTheme = this.checked ? 'dark' : 'light';
                localStorage.setItem('theme', newTheme);
                applyTheme(newTheme);
            });
        }

        const animatedText = document.getElementById('animated-subtitle');
        if(animatedText) {
            const textOptions = [
                "Temukan sumber referensi untuk menunjang perkuliahan Anda.",
                "Jelajahi ribuan e-book dari berbagai kategori.",
                "Bergabunglah dalam diskusi untuk setiap buku.",
                "Pengetahuan ada di ujung jarimu."
            ];
            let currentIndex = 0;
            function changeText() {
                animatedText.style.opacity = 0;
                setTimeout(() => {
                    currentIndex = (currentIndex + 1) % textOptions.length;
                    animatedText.textContent = textOptions[currentIndex];
                    animatedText.style.opacity = 1;
                }, 500);
            }
            setInterval(changeText, 4000);
        }
    });
  </script>

</body>
</html>
<?php
$stmt_data->close();
$conn->close();
?>