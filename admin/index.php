<?php
session_start();
include '../includes/db.php';

// Keamanan: Pastikan hanya admin yang bisa mengakses
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// --- AKSI ADMIN ---

// [DIPERBAIKI] Logika untuk Edit Mahasiswa
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'edit_mahasiswa') {
    $nim_lama = $_POST['nim_lama'];
    $nim_baru = $_POST['nim_baru'];
    $nama_lengkap = $_POST['nama_lengkap'];

    if (!empty($nim_lama) && !empty($nim_baru) && !empty($nama_lengkap)) {
        if ($nim_lama !== $nim_baru) {
            $stmt_check_mhs = $conn->prepare("SELECT nim FROM mahasiswa_resmi WHERE nim = ?");
            $stmt_check_mhs->bind_param("s", $nim_baru);
            $stmt_check_mhs->execute();
            $stmt_check_mhs->store_result();
            if ($stmt_check_mhs->num_rows > 0) {
                $stmt_check_mhs->close();
                header("Location: index.php?status=nim_sudah_ada&nim=" . urlencode($nim_baru) . "#kelola-mahasiswa");
                exit;
            }
            $stmt_check_mhs->close();

            $stmt_check_user = $conn->prepare("SELECT nim FROM users WHERE nim = ?");
            $stmt_check_user->bind_param("s", $nim_baru);
            $stmt_check_user->execute();
            $stmt_check_user->store_result();
            if ($stmt_check_user->num_rows > 0) {
                $stmt_check_user->close();
                header("Location: index.php?status=nim_dipakai_user&nim=" . urlencode($nim_baru) . "#kelola-mahasiswa");
                exit;
            }
            $stmt_check_user->close();
        }

        $stmt_update = $conn->prepare("UPDATE mahasiswa_resmi SET nim = ?, nama_lengkap = ? WHERE nim = ?");
        $stmt_update->bind_param("sss", $nim_baru, $nama_lengkap, $nim_lama);
        if ($stmt_update->execute()) {
            $stmt_update->close();
            header("Location: index.php?status=edit_mhs_sukses#kelola-mahasiswa");
            exit;
        } else {
            $stmt_update->close();
            header("Location: index.php?status=gagal_update_mhs#kelola-mahasiswa");
            exit;
        }
    }
}


// Logika untuk Edit Pengguna (NIM tidak diubah di sini)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'edit_user') {
    $user_id = $_POST['user_id'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    // NIM tidak lagi diambil dari form ini untuk di-update

    if (!empty($user_id) && !empty($username) && !empty($email)) {
        // Query UPDATE tanpa mengubah NIM
        $stmt = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
        $stmt->bind_param("ssi", $username, $email, $user_id);
        $stmt->execute();
        $stmt->close();
        header("Location: index.php?status=edit_user_sukses#kelola-akun");
        exit;
    }
}

// Logika Tambah Buku
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'add_book') {
    // ... (kode tambah buku tidak berubah) ...
    $judul = $_POST['judul'];
    $penulis = $_POST['penulis'];
    $penerbit = $_POST['penerbit'];
    $tahun_terbit = $_POST['tahun_terbit'];
    $isbn = $_POST['isbn'];
    $kategori = $_POST['kategori'];
    $deskripsi = $_POST['deskripsi'];
    $cover_path_for_db = 'Gambar/Covers/' . time() . '_' . basename($_FILES['cover']['name']);
    $cover_path_for_upload = '../' . $cover_path_for_db;
    move_uploaded_file($_FILES['cover']['tmp_name'], $cover_path_for_upload);
    $file_path_for_db = 'ebooks/' . time() . '_' . basename($_FILES['file_buku']['name']);
    $file_path_for_upload = '../' . $file_path_for_db;
    $stmt = $conn->prepare("INSERT INTO buku (judul, penulis, penerbit, tahun_terbit, isbn, kategori, deskripsi, cover_path, file_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssisssss", $judul, $penulis, $penerbit, $tahun_terbit, $isbn, $kategori, $deskripsi, $cover_path_for_db, $file_path_for_db);
    $stmt->execute();
    $stmt->close();
    header("Location: index.php?status=tambah_buku_sukses#kelola-buku");
    exit;
}

// Logika Hapus Buku
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['action']) && $_GET['action'] == 'delete_book') {
    // ... (kode hapus buku tidak berubah) ...
    $id = $_GET['id'];
    $stmt = $conn->prepare("SELECT cover_path, file_path FROM buku WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    if ($result) {
        if ($result['cover_path'] && file_exists('../' . $result['cover_path'])) { unlink('../' . $result['cover_path']); }
        if ($result['file_path'] && file_exists('../' . $result['file_path'])) { unlink('../' . $result['file_path']); }
    }
    $stmt->close();
    $stmt = $conn->prepare("DELETE FROM buku WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: index.php?status=hapus_buku_sukses#kelola-buku");
    exit;
}

// Logika Tambah Mahasiswa
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'add_mahasiswa') {
    $nim = $_POST['nim'];
    $nama_lengkap = $_POST['nama_lengkap'];
    if (!empty($nim) && !empty($nama_lengkap)) {
        $stmt_check = $conn->prepare("SELECT nim FROM mahasiswa_resmi WHERE nim = ?");
        $stmt_check->bind_param("s", $nim);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            $stmt_check->close();
            header("Location: index.php?status=nim_sudah_ada&nim=" . urlencode($nim) . "#kelola-mahasiswa");
            exit;
        }
        $stmt_check->close();
        
        $stmt_insert = $conn->prepare("INSERT INTO mahasiswa_resmi (nim, nama_lengkap) VALUES (?, ?)");
        $stmt_insert->bind_param("ss", $nim, $nama_lengkap);
        
        if ($stmt_insert->execute()) {
            $stmt_insert->close();
            header("Location: index.php?status=tambah_mhs_sukses#kelola-mahasiswa");
            exit;
        } else {
            $stmt_insert->close();
            header("Location: index.php?status=gagal_insert#kelola-mahasiswa");
            exit;
        }
    }
}

// Logika Hapus Mahasiswa
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['action']) && $_GET['action'] == 'delete_mahasiswa') {
    // ... (kode hapus mahasiswa tidak berubah) ...
    $nim = $_GET['nim'];
    $stmt = $conn->prepare("DELETE FROM mahasiswa_resmi WHERE nim = ?");
    $stmt->bind_param("s", $nim);
    $stmt->execute();
    $stmt->close();
    header("Location: index.php?status=hapus_mhs_sukses#kelola-mahasiswa");
    exit;
}

// Logika Hapus User
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['action']) && $_GET['action'] == 'delete_user') {
    // ... (kode hapus user tidak berubah) ...
    $id = $_GET['id'];
    if ($id != $_SESSION['user_id']) {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: index.php?status=hapus_user_sukses#kelola-akun");
    exit;
}

// Logika Update Role
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'update_role') {
    // ... (kode update role tidak berubah) ...
    $id = $_POST['user_id'];
    $role = $_POST['role'];
    if ($id != $_SESSION['user_id']) {
        $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->bind_param("si", $role, $id);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: index.php?status=update_role_sukses#kelola-akun");
    exit;
}

// --- PENGAMBILAN DATA & LOGIKA PENCARIAN ---
// ... (semua query SELECT tidak berubah) ...
$total_users = $conn->query("SELECT COUNT(id) as total FROM users")->fetch_assoc()['total'];
$total_mahasiswa_resmi = $conn->query("SELECT COUNT(nim) as total FROM mahasiswa_resmi")->fetch_assoc()['total'];
$total_buku = $conn->query("SELECT COUNT(id) as total FROM buku")->fetch_assoc()['total'];

$search_buku_query = isset($_GET['search_buku']) ? trim($_GET['search_buku']) : '';
$sql_buku = "SELECT id, judul, penulis, cover_path FROM buku";
if (!empty($search_buku_query)) {
    $sql_buku .= " WHERE judul LIKE '%" . $conn->real_escape_string($search_buku_query) . "%' OR penulis LIKE '%" . $conn->real_escape_string($search_buku_query) . "%'";
}
$sql_buku .= " ORDER BY id DESC";
$buku_result = $conn->query($sql_buku);

$search_mhs_query = isset($_GET['search_mhs']) ? trim($_GET['search_mhs']) : '';
$sql_mahasiswa = "SELECT nim, nama_lengkap FROM mahasiswa_resmi";
if (!empty($search_mhs_query)) {
    $sql_mahasiswa .= " WHERE nim LIKE '%" . $conn->real_escape_string($search_mhs_query) . "%' OR nama_lengkap LIKE '%" . $conn->real_escape_string($search_mhs_query) . "%'";
}
$sql_mahasiswa .= " ORDER BY nim ASC";
$mahasiswa_resmi_result = $conn->query($sql_mahasiswa);

$search_user_query = isset($_GET['search_user']) ? trim($_GET['search_user']) : '';
$sql_users = "SELECT id, username, email, nim, role FROM users";
if (!empty($search_user_query)) {
    $sql_users .= " WHERE username LIKE '%" . $conn->real_escape_string($search_user_query) . "%' OR email LIKE '%" . $conn->real_escape_string($search_user_query) . "%' OR nim LIKE '%" . $conn->real_escape_string($search_user_query) . "%'";
}
$sql_users .= " ORDER BY id ASC";
$users_result = $conn->query($sql_users);

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
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
        /* ... (CSS tidak berubah) ... */
        :root {
            --light-bg: #f0f2f5;
            --dark-bg: #18191a;
            --dark-surface: #242526;
            --dark-surface-2: #3a3b3c;
            --dark-text-primary: #e4e6eb;
            --dark-text-secondary: #b0b3b8;
        }
        body { background-color: var(--light-bg); }
        .wrapper { display: flex; width: 100%; }
        .sidebar { width: 260px; background: #212529; color: white; position: fixed; height: 100%; padding: 1.5rem 1rem; flex-shrink: 0; }
        .sidebar .nav-link { color: #adb5bd; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { color: #fff; background-color: #495057; }
        .sidebar .sidebar-header { font-size: 1.5rem; font-weight: bold; text-align: center; margin-bottom: 2rem; }
        .main-content { padding: 2rem; width: calc(100% - 260px); margin-left: 260px; }
        .stat-card { border-radius: 0.75rem; padding: 1.5rem; display: flex; align-items: center; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .card-users, .card-mahasiswa, .card-buku { color: white; }
        .card-users { background: linear-gradient(135deg, #0d6efd, #0a58ca); }
        .card-mahasiswa { background: linear-gradient(135deg, #0dcaf0, #0aa3c2); }
        .card-buku { background: linear-gradient(135deg, #198754, #146c43); }
        .stat-card .icon { font-size: 2.5rem; padding: 1rem; border-radius: 50%; margin-right: 1rem; background-color: rgba(255, 255, 255, 0.2); display: flex; align-items: center; justify-content: center; }
        .admin-card { 
            background-color: #fff; 
            border-radius: 0.75rem; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            border: 1px solid #dee2e6;
        }
        .table-custom img { width: 45px; height: 60px; object-fit: cover; border-radius: 0.25rem; }
        .table-scroll-container { max-height: 450px; overflow-y: auto; }
        .table-scroll-container thead th { position: sticky; top: 0; z-index: 1; }
        html.dark-mode body { background-color: var(--dark-bg); color: var(--dark-text-primary); }
        html.dark-mode .admin-card { 
            background-color: var(--dark-surface); 
            border: 1px solid #4a4a4d;
        }
        html.dark-mode .card-header.bg-white { background-color: var(--dark-surface) !important; border-bottom: 1px solid var(--dark-surface-2); }
        html.dark-mode h1, html.dark-mode h2, html.dark-mode h4, html.dark-mode h5 { color: var(--dark-text-primary); }
        html.dark-mode .form-label { color: var(--dark-text-secondary); }
        html.dark-mode .form-control, html.dark-mode .form-select, html.dark-mode .modal-content { background-color: var(--dark-surface-2); color: var(--dark-text-primary); border-color: #4a4a4d; }
        html.dark-mode .form-control:focus, html.dark-mode .form-select:focus { border-color: #0d6efd; box-shadow: 0 0 0 .25rem rgba(13,110,253,.25); }
        html.dark-mode .form-control::placeholder { color: #888; }
        html.dark-mode .input-group-text { background-color: var(--dark-surface-2); border-color: #4a4a4d; }
        html.dark-mode hr, html.dark-mode .modal-header, html.dark-mode .modal-footer { border-color: var(--dark-surface-2); }
        html.dark-mode .table { --bs-table-color: var(--dark-text-secondary); --bs-table-bg: transparent; --bs-table-border-color: var(--dark-surface-2); --bs-table-hover-bg: #323539; --bs-table-hover-color: #fff; }
        html.dark-mode .table-striped > tbody > tr:nth-of-type(odd) > * { --bs-table-accent-bg: rgba(255, 255, 255, 0.05); }
        html.dark-mode .table b, html.dark-mode .table strong { color: var(--dark-text-primary); }
        html.dark-mode .table-scroll-container thead th { background-color: #343a40; }
        html.dark-mode .btn-close { filter: invert(1) grayscale(100%); }
        @media (max-width: 991.98px) {
            .wrapper { flex-direction: column; }
            .sidebar { position: static; width: 100%; height: auto; margin-bottom: 1rem; }
            .main-content { margin-left: 0; width: 100%; padding: 1rem; }
        }
    </style>
</head>
<body>

<div class="wrapper">
    <div class="sidebar">
        <div class="sidebar-header"><i class="bi bi-person-workspace"></i> Admin</div>
        <ul class="nav flex-column">
            <li class="nav-item"><a class="nav-link active" href="#kelola-buku"><i class="bi bi-book-half me-2"></i> Kelola Buku</a></li>
            <li class="nav-item"><a class="nav-link" href="#kelola-mahasiswa"><i class="bi bi-database-gear me-2"></i> Kelola Mahasiswa</a></li>
            <li class="nav-item"><a class="nav-link" href="#kelola-akun"><i class="bi bi-person-gear me-2"></i> Kelola Akun</a></li>
            <li class="nav-item mt-3"><a class="nav-link" href="../pages/dashboard.php"><i class="bi bi-arrow-left-circle me-2"></i> Kembali ke Situs</a></li>
        </ul>
    </div>
    <div class="main-content">
        
        <?php
        if (isset($_GET['status'])) {
            $status = $_GET['status']; $message = ''; $alert_type = 'success';
            switch ($status) {
                case 'edit_sukses': $message = '<strong>Berhasil!</strong> Data buku telah berhasil diperbarui.'; break;
                case 'tambah_buku_sukses': $message = '<strong>Berhasil!</strong> Buku baru telah ditambahkan ke koleksi.'; break;
                case 'hapus_buku_sukses': $message = '<strong>Berhasil!</strong> Buku telah dihapus dari koleksi.'; break;
                case 'tambah_mhs_sukses': $message = '<strong>Berhasil!</strong> Mahasiswa resmi telah ditambahkan.'; break;
                case 'hapus_mhs_sukses': $message = '<strong>Berhasil!</strong> Data mahasiswa telah dihapus.'; break;
                case 'update_role_sukses': $message = '<strong>Berhasil!</strong> Peran pengguna telah diperbarui.'; break;
                case 'hapus_user_sukses': $message = '<strong>Berhasil!</strong> Akun pengguna telah dihapus.'; break;
                case 'edit_mhs_sukses': $message = '<strong>Berhasil!</strong> Data mahasiswa telah diperbarui.'; break;
                case 'edit_user_sukses': $message = '<strong>Berhasil!</strong> Data pengguna telah diperbarui.'; break;
                case 'nim_sudah_ada':
                    $nim_error = isset($_GET['nim']) ? htmlspecialchars($_GET['nim']) : '';
                    $message = '<strong>Gagal!</strong> NIM <strong>' . $nim_error . '</strong> sudah terdaftar di sistem.';
                    $alert_type = 'danger';
                    break;
                case 'nim_dipakai_user':
                    $nim_error = isset($_GET['nim']) ? htmlspecialchars($_GET['nim']) : '';
                    $message = '<strong>Gagal!</strong> NIM <strong>' . $nim_error . '</strong> sudah digunakan oleh akun lain.';
                    $alert_type = 'danger';
                    break;
            }
            if ($message) { echo '<div class="alert alert-' . $alert_type . ' alert-dismissible fade show" role="alert">' . $message . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>'; }
        }
        ?>

        <h1 class="h2 mb-4">Dashboard Admin</h1>

        <div class="row g-4 mb-4">
            <div class="col-md-4"><div class="stat-card card-users"><div class="icon"><i class="bi bi-people-fill"></i></div><div><h2 class="fw-bold"><?= $total_users ?></h2><p class="mb-0 text-muted">Total Akun Terdaftar</p></div></div></div>
            <div class="col-md-4"><div class="stat-card card-mahasiswa"><div class="icon"><i class="bi bi-person-badge"></i></div><div><h2 class="fw-bold"><?= $total_mahasiswa_resmi ?></h2><p class="mb-0 text-muted">Total Mahasiswa Resmi</p></div></div></div>
            <div class="col-md-4"><div class="stat-card card-buku"><div class="icon"><i class="bi bi-book-half"></i></div><div><h2 class="fw-bold"><?= $total_buku ?></h2><p class="mb-0 text-muted">Total Buku</p></div></div></div>
        </div>

        <div class="admin-card mb-4" id="kelola-buku">
            <div class="card-header bg-white py-3"><h4 class="mb-0"><i class="bi bi-book-half me-2"></i> Kelola Buku</h4></div>
            <div class="card-body p-4">
                <h5 class="mb-3">Tambah Buku Baru</h5>
                <form action="index.php" method="POST" enctype="multipart/form-data" class="mb-4">
                    <input type="hidden" name="action" value="add_book">
                    <div class="row g-3"><div class="col-md-8 mb-3"><label class="form-label">Judul Buku</label><input type="text" name="judul" class="form-control" required></div><div class="col-md-4 mb-3"><label class="form-label">Penulis</label><input type="text" name="penulis" class="form-control" required></div></div>
                    <div class="row g-3"><div class="col-md-4 mb-3"><label class="form-label">Penerbit</label><input type="text" name="penerbit" class="form-control"></div><div class="col-md-2 mb-3"><label class="form-label">Tahun</label><input type="number" name="tahun_terbit" class="form-control" placeholder="Contoh: 2023"></div><div class="col-md-3 mb-3"><label class="form-label">ISBN</label><input type="text" name="isbn" class="form-control"></div><div class="col-md-3 mb-3"><label class="form-label">Kategori</label><input type="text" name="kategori" class="form-control"></div></div>
                    <div class="mb-3"><label class="form-label">Deskripsi Singkat</label><textarea name="deskripsi" class="form-control" rows="3"></textarea></div>
                    <div class="row g-3"><div class="col-md-6 mb-3"><label class="form-label">File Cover (Gambar)</label><input type="file" name="cover" class="form-control" accept="image/*" required></div><div class="col-md-6 mb-3"><label class="form-label">File Buku (PDF)</label><input type="file" name="file_buku" class="form-control" accept=".pdf" required></div></div>
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-plus-circle me-2"></i>Tambah Buku</button>
                </form>
                <hr class="my-4">
                <h5>Daftar Buku di Perpustakaan</h5>
                <form action="#kelola-buku" method="GET" class="mb-3">
                    <div class="input-group">
                        <input type="text" name="search_buku" class="form-control" placeholder="Cari Judul atau Penulis Buku..." value="<?= htmlspecialchars($search_buku_query) ?>">
                        <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i></button>
                    </div>
                </form>
                <div class="table-scroll-container">
                    <table class="table table-custom table-hover table-striped mb-0">
                        <thead><tr><th>Cover</th><th>Judul</th><th>Penulis</th><th class="text-end">Aksi</th></tr></thead>
                        <tbody>
                            <?php while($row = $buku_result->fetch_assoc()): ?>
                            <tr>
                                <td><img src="../<?= htmlspecialchars($row['cover_path']) ?>" alt="cover"></td>
                                <td><b><?= htmlspecialchars($row['judul']) ?></b></td>
                                <td><?= htmlspecialchars($row['penulis']) ?></td>
                                <td class="text-end">
                                    <a href="edit_buku.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm" title="Edit"><i class="bi bi-pencil-square"></i> Edit Halaman</a>
                                    <a href="index.php?action=delete_book&id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin?')" title="Hapus"><i class="bi bi-trash3-fill"></i> Hapus</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="admin-card mb-4" id="kelola-mahasiswa">
            <div class="card-header bg-white py-3"><h4 class="mb-0"><i class="bi bi-database-gear me-2"></i> Kelola Mahasiswa Resmi</h4></div>
            <div class="card-body p-4">
                <h5 class="mb-3">Tambah Mahasiswa Baru</h5>
                <form action="index.php" method="POST" class="mb-4">
                    <input type="hidden" name="action" value="add_mahasiswa">
                    <div class="row g-2">
                        <div class="col-md-5"><input type="text" name="nim" class="form-control" placeholder="NIM" required></div>
                        <div class="col-md-5"><input type="text" name="nama_lengkap" class="form-control" placeholder="Nama Lengkap" required></div>
                        <div class="col-md-2"><button type="submit" class="btn btn-primary w-100">Tambah</button></div>
                    </div>
                </form>
                <hr class="my-4">
                <h5>Daftar Mahasiswa Resmi</h5>
                <form action="#kelola-mahasiswa" method="GET" class="mb-3">
                    <div class="input-group">
                        <input type="text" name="search_mhs" class="form-control" placeholder="Cari NIM atau Nama Mahasiswa..." value="<?= htmlspecialchars($search_mhs_query) ?>">
                        <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i></button>
                    </div>
                </form>
                <div class="table-scroll-container">
                    <table class="table table-custom table-hover table-striped mb-0">
                        <thead><tr><th>NIM</th><th>Nama Lengkap</th><th class="text-end">Aksi</th></tr></thead>
                        <tbody>
                            <?php while($row = $mahasiswa_resmi_result->fetch_assoc()): ?>
                            <tr>
                                <td data-label="NIM"><b><?= htmlspecialchars($row['nim']) ?></b></td>
                                <td data-label="Nama"><?= htmlspecialchars($row['nama_lengkap']) ?></td>
                                <td class="text-end">
                                    <button class="btn btn-warning btn-sm edit-mhs-btn" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editMahasiswaModal"
                                            data-nim="<?= htmlspecialchars($row['nim']) ?>"
                                            data-nama="<?= htmlspecialchars($row['nama_lengkap']) ?>">
                                        <i class="bi bi-pencil-square"></i> Edit
                                    </button>
                                    <a href="index.php?action=delete_mahasiswa&nim=<?= urlencode($row['nim']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin?')"><i class="bi bi-trash3-fill"></i> Hapus</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="admin-card" id="kelola-akun">
            <div class="card-header bg-white py-3"><h4 class="mb-0"><i class="bi bi-person-gear me-2"></i> Kelola Akun Pengguna</h4></div>
            <div class="card-body p-4">
                <form action="#kelola-akun" method="GET" class="mb-3">
                    <div class="input-group">
                        <input type="text" name="search_user" class="form-control" placeholder="Cari Username, Email, atau NIM..." value="<?= htmlspecialchars($search_user_query) ?>">
                        <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i></button>
                    </div>
                </form>
                <div class="table-scroll-container">
                    <table class="table table-custom table-hover table-striped mb-0">
                        <thead><tr><th>ID</th><th>Username</th><th>Email</th><th>NIM</th><th>Role</th><th class="text-end">Aksi</th></tr></thead>
                        <tbody>
                            <?php while($row = $users_result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['id'] ?></td>
                                <td data-label="Username"><b><?= htmlspecialchars($row['username']) ?></b></td>
                                <td data-label="Email"><?= htmlspecialchars($row['email']) ?></td>
                                <td data-label="NIM"><?= htmlspecialchars($row['nim'] ?? 'N/A') ?></td>
                                <td>
                                    <form action="index.php" method="POST" style="min-width: 100px;">
                                        <input type="hidden" name="action" value="update_role">
                                        <input type="hidden" name="user_id" value="<?= $row['id'] ?>">
                                        <select name="role" class="form-select form-select-sm" onchange="this.form.submit()" <?= ($row['id'] == $_SESSION['user_id']) ? 'disabled' : '' ?>>
                                            <option value="user" <?= ($row['role'] == 'user') ? 'selected' : '' ?>>User</option>
                                            <option value="admin" <?= ($row['role'] == 'admin') ? 'selected' : '' ?>>Admin</option>
                                        </select>
                                    </form>
                                </td>
                                <td class="text-end">
                                    <?php if ($row['id'] != $_SESSION['user_id']): ?>
                                    <button class="btn btn-warning btn-sm edit-user-btn"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editUserModal"
                                            data-id="<?= $row['id'] ?>"
                                            data-username="<?= htmlspecialchars($row['username']) ?>"
                                            data-email="<?= htmlspecialchars($row['email']) ?>"
                                            data-nim="<?= htmlspecialchars($row['nim'] ?? '') ?>">
                                        <i class="bi bi-pencil-square"></i> Edit
                                    </button>
                                    <a href="index.php?action=delete_user&id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin?')"><i class="bi bi-trash3-fill"></i> Hapus</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editMahasiswaModal" tabindex="-1" aria-labelledby="editMahasiswaModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editMahasiswaModalLabel">Edit Mahasiswa Resmi</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="index.php" method="POST">
        <div class="modal-body">
            <input type="hidden" name="action" value="edit_mahasiswa">
            <input type="hidden" id="edit-nim-lama" name="nim_lama">
            <div class="mb-3">
                <label for="edit-nim-baru" class="form-label">NIM</label>
                <input type="text" class="form-control" id="edit-nim-baru" name="nim_baru" required>
            </div>
            <div class="mb-3">
                <label for="edit-nama-lengkap" class="form-label">Nama Lengkap</label>
                <input type="text" class="form-control" id="edit-nama-lengkap" name="nama_lengkap" required>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editUserModalLabel">Edit Pengguna</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="index.php" method="POST">
        <div class="modal-body">
            <input type="hidden" name="action" value="edit_user">
            <input type="hidden" id="edit-user-id" name="user_id">
            <div class="mb-3">
                <label for="edit-username" class="form-label">Username</label>
                <input type="text" class="form-control" id="edit-username" name="username" required>
            </div>
            <div class="mb-3">
                <label for="edit-email" class="form-label">Email</label>
                <input type="email" class="form-control" id="edit-email" name="email" required>
            </div>
            <div class="mb-3">
                <label for="edit-nim-user" class="form-label">NIM (Tidak dapat diubah)</label>
                <input type="text" class="form-control" id="edit-nim-user" name="nim" readonly>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </div>
      </form>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // ... (Script tidak berubah) ...
    var editMahasiswaModal = document.getElementById('editMahasiswaModal');
    if(editMahasiswaModal) {
        editMahasiswaModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var nim = button.getAttribute('data-nim');
            var nama = button.getAttribute('data-nama');

            var modalNimLamaInput = editMahasiswaModal.querySelector('#edit-nim-lama');
            var modalNimBaruInput = editMahasiswaModal.querySelector('#edit-nim-baru');
            var modalNamaInput = editMahasiswaModal.querySelector('#edit-nama-lengkap');

            modalNimLamaInput.value = nim;
            modalNimBaruInput.value = nim;
            modalNamaInput.value = nama;
        });
    }

    var editUserModal = document.getElementById('editUserModal');
    if(editUserModal) {
        editUserModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var id = button.getAttribute('data-id');
            var username = button.getAttribute('data-username');
            var email = button.getAttribute('data-email');
            var nim = button.getAttribute('data-nim');

            var modalUserIdInput = editUserModal.querySelector('#edit-user-id');
            var modalUsernameInput = editUserModal.querySelector('#edit-username');
            var modalEmailInput = editUserModal.querySelector('#edit-email');
            var modalNimUserInput = editUserModal.querySelector('#edit-nim-user');
            
            modalUserIdInput.value = id;
            modalUsernameInput.value = username;
            modalEmailInput.value = email;
            modalNimUserInput.value = nim;
        });
    }
});
</script>
</body>
</html>
<?php
$conn->close();
?>