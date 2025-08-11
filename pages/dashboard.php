<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$namaPengguna = htmlspecialchars($_SESSION['username']);
// Ambil buku, kita butuh judulnya sekarang
$books_result = $conn->query("SELECT id, judul, cover_path FROM buku ORDER BY id DESC LIMIT 12"); 
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Perpustakaan Digital</title>
<link href="../css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <style>
    /* ... (CSS dari sebelumnya tetap sama) ... */
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
    
    /* [PERUBAHAN] Penyesuaian untuk kartu buku dan judul */
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
        /* Mencegah judul yang terlalu panjang merusak layout */
        overflow: hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        min-height: 42px; /* Memberi ruang untuk 2 baris judul */
    }
  </style>
</head>
<body>

  <nav class="navbar navbar-expand-lg navbar-dark bg-dark bg-opacity-75 fixed-top">
    <div class="container">
      <a class="navbar-brand" href="dashboard.php"><i class="bi bi-book-half"></i> Perpus Digital</a>
      <div class="collapse navbar-collapse" id="navbarNavDropdown">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" role="button" data-bs-toggle="dropdown"><i class="bi bi-person-circle"></i> <?= $namaPengguna ?></a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="akun.php">Profile Saya</a></li>          
<?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
  <li><a class="dropdown-item" href="../admin/">Admin Panel</a></li> <?php endif; ?>
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
        <form action="#" method="GET" class="search-form">
          <div class="input-group"><input type="text" class="form-control form-control-lg" placeholder="Masukkan judul buku, penulis, atau kata kunci..."><button class="btn btn-primary px-4" type="submit"><i class="bi bi-search"></i></button></div>
        </form>
      </div>
    </div>
  </header>

  <main class="container my-5">
    <section class="book-collection">
      <h2 class="section-title">Koleksi Terbaru</h2>
      <div class="row row-cols-2 row-cols-md-4 row-cols-lg-6 g-4">
        
        <?php if ($books_result->num_rows > 0): ?>
          <?php while($book = $books_result->fetch_assoc()): ?>
          <div class="col">
            <div class="book-item text-center">
              <a href="detail_buku.php?id=<?= $book['id'] ?>">
                <div class="card shadow-sm">
                    <img src="<?= htmlspecialchars($book['cover_path']) ?>" class="book-cover" alt="Cover Buku">
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
            <p class="text-muted">Koleksi buku masih kosong.</p>
          </div>
        <?php endif; ?>
        </div>
    </section>
  </main>

  <footer class="text-center py-4 mt-5 bg-white border-top">
    <p class="mb-0">&copy; 2025 Perpustakaan Digital Polibatam. All Rights Reserved.</p>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
$conn->close();
?>