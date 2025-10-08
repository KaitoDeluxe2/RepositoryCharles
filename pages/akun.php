<?php
session_start();
include '../includes/db.php'; // Pastikan path ini benar

// Jika pengguna belum login, arahkan ke halaman login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = "";
$message_type = "success";

// --- LOGIKA UPDATE PROFIL ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'update_profile') {
    $name = $_POST['name'];
    $email = $_POST['email'];

    if (!empty($name) && !empty($email)) {
        $stmt_check = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt_check->bind_param("si", $email, $user_id);
        $stmt_check->execute();
        if ($stmt_check->get_result()->num_rows > 0) {
            $message = "Error: Email tersebut sudah digunakan oleh akun lain.";
            $message_type = "danger";
        } else {
            $stmt_update = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
            $stmt_update->bind_param("ssi", $name, $email, $user_id);

            if ($stmt_update->execute()) {
                $_SESSION['username'] = $name;
                $_SESSION['email'] = $email;
                $message = "Perubahan berhasil disimpan!";
                $message_type = "success";
            } else {
                $message = "Error: Gagal menyimpan perubahan.";
                $message_type = "danger";
            }
            $stmt_update->close();
        }
        $stmt_check->close();
    } else {
        $message = "Error: Nama Pengguna dan Email tidak boleh kosong.";
        $message_type = "danger";
    }
}

// --- AMBIL DATA PENGGUNA & STATISTIK ---
// Ambil data lengkap pengguna dari database, termasuk tanggal bergabung
$stmt_user = $conn->prepare("SELECT username, email, nim, role, avatar_seed, bergabung_sejak FROM users WHERE id = ?");
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$user_data = $stmt_user->get_result()->fetch_assoc();
$stmt_user->close();

$current_name = htmlspecialchars($user_data['username']);
$current_email = htmlspecialchars($user_data['email']);
$current_nim = htmlspecialchars($user_data['nim'] ?? 'N/A');
$current_role = $user_data['role'];
$avatar_seed = $user_data['avatar_seed'];
$tanggal_bergabung = date('d M Y', strtotime($user_data['bergabung_sejak']));

// Statistik: Hitung jumlah total komentar
$stmt_diskusi = $conn->prepare("SELECT COUNT(id) as total_diskusi FROM diskusi WHERE user_id = ?");
$stmt_diskusi->bind_param("i", $user_id);
$stmt_diskusi->execute();
$total_diskusi = $stmt_diskusi->get_result()->fetch_assoc()['total_diskusi'];
$stmt_diskusi->close();

// Statistik: Hitung jumlah total suka yang diterima
$stmt_likes = $conn->prepare("SELECT SUM(likes) as total_suka FROM diskusi WHERE user_id = ?");
$stmt_likes->bind_param("i", $user_id);
$stmt_likes->execute();
$total_suka = $stmt_likes->get_result()->fetch_assoc()['total_suka'] ?? 0;
$stmt_likes->close();


// --- LOGIKA PAGINASI UNTUK AKTIVITAS ---
$item_per_halaman = 5;
$halaman_aktif = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($halaman_aktif - 1) * $item_per_halaman;
$total_halaman = ceil($total_diskusi / $item_per_halaman);

// Aktivitas: Ambil komentar terakhir dari pengguna sesuai halaman
$stmt_activity = $conn->prepare(
    "SELECT d.komentar, d.tanggal, b.judul as judul_buku, b.id as buku_id 
     FROM diskusi d 
     JOIN buku b ON d.buku_id = b.id 
     WHERE d.user_id = ? 
     ORDER BY d.tanggal DESC 
     LIMIT ? OFFSET ?"
);
$stmt_activity->bind_param("iii", $user_id, $item_per_halaman, $offset);
$stmt_activity->execute();
$activities = $stmt_activity->get_result();
$stmt_activity->close();

$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Profil <?= $current_name ?></title>
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
        /* ... CSS Anda yang lain tetap sama ... */
        body { background-color: #f0f2f5; }
        .profile-header { background-color: #fff; padding: 2.5rem; border-radius: 1rem; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
        .profile-avatar { width: 120px; height: 120px; border-radius: 50%; box-shadow: 0 4px 15px rgba(0,0,0,0.1); overflow: hidden; }
        .stat-item { text-align: center; }
        .stat-item .stat-count { font-size: 1.5rem; font-weight: 700; }
        .stat-item .stat-label { font-size: 0.9rem; color: #6c757d; }
        .activity-feed .list-group-item { border-left-width: 4px; border-left-color: #0d6efd; }
        html.dark-mode body { background-color: #18191a; color: #e4e6eb; }
        html.dark-mode .profile-header, html.dark-mode .card, html.dark-mode .nav-tabs .nav-link { background-color: #242526; border-color: #3a3b3c; }
        html.dark-mode h2, html.dark-mode h4 { color: #e4e6eb; }
        html.dark-mode .text-muted, html.dark-mode .stat-label { color: #b0b3b8 !important; }
        html.dark-mode .nav-tabs .nav-link { color: #b0b3b8; }
        html.dark-mode .nav-tabs .nav-link.active { color: #e4e6eb; background-color: #3a3b3c; border-bottom-color: #3a3b3c; }
        html.dark-mode .list-group-item { background-color: #3a3b3c; border-color: #4a4a4d; }
        html.dark-mode .form-control { background-color: #3a3b3c; color: #e4e6eb; border-color: #4a4a4d; }
        html.dark-mode .btn-outline-secondary { color: #e4e6eb; border-color: #6c757d; }
        html.dark-mode .btn-outline-secondary:hover { background-color: #6c757d; color: #fff; }
        html.dark-mode .form-label { color: #b0b3b8; }
        html.dark-mode .pagination .page-link { background-color: #242526; border-color: #3a3b3c; color: #0d6efd; }
        html.dark-mode .pagination .page-item.disabled .page-link { background-color: #3a3b3c; color: #6c757d; }
        html.dark-mode .pagination .page-item.active .page-link { background-color: #0d6efd; border-color: #0d6efd; }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="col-lg-9 mx-auto">
        <div class="mb-4">
            <a href="dashboard.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Kembali ke Dashboard</a>
        </div>
        <div class="profile-header mb-4">
            <div class="row align-items-center">
                <div class="col-md-3 text-center">
                    <img src="https://api.dicebear.com/8.x/croodles/svg?seed=<?= urlencode($avatar_seed) ?>" alt="Avatar Pengguna" class="profile-avatar">
                </div>
                <div class="col-md-9">
                    <h2 class="fw-bold mb-1"><?= $current_name ?></h2>
                    <p class="text-muted mb-2"><?= $current_email ?></p>
                    <?php if ($current_role !== 'admin' && $current_nim !== 'N/A'): ?>
                        <p class="text-muted small">NIM: <?= $current_nim ?></p>
                    <?php endif; ?>
                    <hr>
                    <div class="row">
                        <div class="col-4 stat-item">
                            <div class="stat-count"><?= $total_diskusi ?></div>
                            <div class="stat-label">Diskusi</div>
                        </div>
                        <div class="col-4 stat-item">
                            <div class="stat-count"><?= $total_suka ?></div>
                            <div class="stat-label">Jumlah Suka</div>
                        </div>
                        <div class="col-4 stat-item">
                            <div class="stat-count"><?= $tanggal_bergabung ?></div>
                            <div class="stat-label">Bergabung</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <ul class="nav nav-tabs nav-fill mb-4" id="profileTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="activity-tab" data-bs-toggle="tab" data-bs-target="#activity" type="button" role="tab">Aktivitas Diskusi</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="settings-tab" data-bs-toggle="tab" data-bs-target="#settings" type="button" role="tab">Pengaturan Akun</button>
            </li>
        </ul>
        <div class="tab-content" id="profileTabContent">
            <div class="tab-pane fade" id="activity" role="tabpanel">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Aktivitas Diskusi Terakhir</h4>
                        <div class="activity-feed">
                            <?php if ($activities->num_rows > 0): ?>
                                <ul class="list-group list-group-flush">
                                <?php while($act = $activities->fetch_assoc()): ?>
                                    <li class="list-group-item mb-3">
                                        <p class="mb-1">Anda berkomentar: "<i><?= htmlspecialchars(substr($act['komentar'], 0, 100)) . (strlen($act['komentar']) > 100 ? '...' : '') ?></i>"</p>
                                        <small class="text-muted">
                                            Pada buku <a href="detail_buku.php?id=<?= $act['buku_id'] ?>"><?= htmlspecialchars($act['judul_buku']) ?></a>
                                            - <?= date('d M Y, H:i', strtotime($act['tanggal'])) ?>
                                        </small>
                                    </li>
                                <?php endwhile; ?>
                                </ul>
                            <?php else: ?>
                                <p class="text-center text-muted">Belum ada aktivitas diskusi.</p>
                            <?php endif; ?>
                        </div>
                        <?php if ($total_halaman > 1): ?>
                        <nav aria-label="Navigasi Aktivitas" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <li class="page-item <?= ($halaman_aktif <= 1) ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?page=<?= $halaman_aktif - 1 ?>">Sebelumnya</a>
                                </li>
                                <li class="page-item <?= ($halaman_aktif >= $total_halaman) ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?page=<?= $halaman_aktif + 1 ?>">Selanjutnya</a>
                                </li>
                            </ul>
                        </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade" id="settings" role="tabpanel">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Edit Informasi Akun</h4>
                        <?php if ($message): ?>
                            <div class="alert alert-<?= $message_type ?>"><?= $message ?></div>
                        <?php endif; ?>
                        <form action="akun.php?tab=settings" method="POST" autocomplete="off">
                            <input type="hidden" name="action" value="update_profile">
                            <div class="mb-3">
                                <label for="name" class="form-label">Nama Pengguna</label>
                                <input type="text" class="form-control" id="name" name="name" value="<?= $current_name ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Alamat Email</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?= $current_email ?>" required>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary"><i class="bi bi-save-fill me-2"></i>Simpan Perubahan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const activeTab = urlParams.get('tab');
        const defaultTab = 'activity';
        const tabToActivate = activeTab || defaultTab;
        document.querySelectorAll('#profileTab .nav-link').forEach(tab => tab.classList.remove('active'));
        document.querySelectorAll('#profileTabContent .tab-pane').forEach(pane => {
            pane.classList.remove('active', 'show');
        });
        const tabElement = document.getElementById(tabToActivate + '-tab');
        const paneElement = document.getElementById(tabToActivate);
        if (tabElement && paneElement) {
            tabElement.classList.add('active');
            paneElement.classList.add('active', 'show');
        }
    });
</script>
</body>
</html>