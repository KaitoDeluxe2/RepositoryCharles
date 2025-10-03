<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$namaPengguna = htmlspecialchars($_SESSION['username']);

$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$is_searching = !empty($search_query);

// --- LOGIKA PAGINASI DAN PENGAMBILAN DATA ---
$buku_per_halaman = 12; // Jumlah buku per halaman

// Tentukan halaman saat ini
$halaman_aktif = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($halaman_aktif < 1) {
    $halaman_aktif = 1;
}

// Persiapkan query dasar
$sql_data = "SELECT id, judul, cover_path, penulis FROM buku";
$sql_count = "SELECT COUNT(id) AS total FROM buku";

$params = [];
$types = "";

// Jika sedang mencari, tambahkan kondisi WHERE
if ($is_searching) {
    $search_term = "%" . $search_query . "%";
    $where_clause = " WHERE judul LIKE ? OR penulis LIKE ?";
    $sql_data .= $where_clause;
    $sql_count .= $where_clause;
    $params[] = &$search_term;
    $params[] = &$search_term;
    $types .= "ss";
}

// Hitung total buku (baik dengan atau tanpa filter pencarian)
$stmt_count = $conn->prepare($sql_count);
if ($is_searching) {
    $stmt_count->bind_param($types, ...$params);
}
$stmt_count->execute();
$total_buku = $stmt_count->get_result()->fetch_assoc()['total'];
$stmt_count->close();

// Hitung total halaman
$total_halaman = ceil($total_buku / $buku_per_halaman);
if ($halaman_aktif > $total_halaman && $total_halaman > 0) {
    $halaman_aktif = $total_halaman;
}

// Hitung OFFSET
$offset = ($halaman_aktif - 1) * $buku_per_halaman;

// Tambahkan ORDER BY dan LIMIT/OFFSET ke query data
$sql_data .= " ORDER BY id DESC LIMIT ? OFFSET ?";
$params[] = &$buku_per_halaman;
$params[] = &$offset;
$types .= "ii";

// Ambil data buku untuk halaman saat ini
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
  <title>Perpustakaan Digital</title>
  <link href="../css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="../css/style-dark.css">
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
    
    .book-item .card {
        transition: transform .2s, box-shadow .2s;
        border: 1px solid #eee;
        background-color: transparent;
    }
    .book-item .card:hover {
        transform: translateY(-8px);
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.1);
    }
    .book-cover {
        height: 280px; object-fit: cover; width: 100%; border-radius: 0.25rem;
    }
    .book-title {
        margin-top: 0.75rem;
        font-weight: 600;
        color: #343a40;
        overflow: hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        min-height: 48px;
    }
  </style>
</head>
<body>

  <nav class="navbar navbar-expand-lg navbar-dark bg-dark bg-opacity-75 fixed-top">
    <div class="container">
      <a class="navbar-brand" href="dashboard.php"><i class="bi bi-book-half"></i> Perpus Digital</a>
      
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      
      <div class="collapse navbar-collapse" id="navbarNavDropdown">
        <ul class="navbar-nav ms-auto align-items-center">
        
          <li class="nav-item me-2">
            <div class="theme-switch-wrapper">
              <i class="bi bi-sun-fill text-white me-2"></i>
              <label class="theme-switch" for="checkbox">
                <input type="checkbox" id="checkbox" />
                <div class="slider round"></div>
              </label>
              <i class="bi bi-moon-fill text-white ms-1"></i>
            </div>
          </li>

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
        </ul>
      </div>
    </div>
  </nav>

  <header class="hero-section">
    <div class="container">
      <h1 class="display-5 fw-bold">Perpustakaan Digital Politeknik Negeri Batam</h1>
      <p class="lead">Temukan sumber referensi untuk menunjang perkuliahan Anda.</p>
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

  <footer class="text-center py-4 mt-5 bg-white border-top">
    <p class="mb-0">&copy; 2025 Perpustakaan Digital Polibatam. All Rights Reserved.</p>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
        const themeToggle = document.getElementById('checkbox');
        const currentTheme = localStorage.getItem('theme');

        if (currentTheme) {
            document.body.classList.add(currentTheme);
            if (currentTheme === 'dark-mode') {
                themeToggle.checked = true;
            }
        }

        themeToggle.addEventListener('change', function () {
            if (this.checked) {
                document.body.classList.add('dark-mode');
                localStorage.setItem('theme', 'dark-mode');
            } else {
                document.body.classList.remove('dark-mode');
                localStorage.setItem('theme', 'light-mode');
            }
        });
    });
  </script>
</body>
</html>
<?php
$stmt_data->close();
$conn->close();
?>