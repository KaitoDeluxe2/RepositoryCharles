<?php
session_start();
// [PATH DIPERBAIKI] - Keluar satu folder untuk mencari 'includes'
include '../includes/db.php';

// Keamanan: Pastikan hanya admin yang bisa mengakses
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    // [PATH DIPERBAIKI] - Arahkan ke login di folder root
    header("Location: ../login.php");
    exit;
}

// --- LOGIKA AKSI ADMIN (TIDAK ADA PERUBAHAN) ---

// Aksi: Menambah Buku Baru
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'add_book') {
    $judul = $_POST['judul'];
    $penulis = $_POST['penulis'];
    $penerbit = $_POST['penerbit'];
    $tahun_terbit = $_POST['tahun_terbit'];
    $isbn = $_POST['isbn'];
    $kategori = $_POST['kategori'];
    $deskripsi = $_POST['deskripsi'];

    // [PATH DIPERBAIKI] - Gunakan path yang benar untuk upload
    $cover_path_for_db = 'Gambar/Covers/' . time() . '_' . basename($_FILES['cover']['name']);
    $cover_path_for_upload = '../' . $cover_path_for_db;
    move_uploaded_file($_FILES['cover']['tmp_name'], $cover_path_for_upload);

    $file_path_for_db = 'ebooks/' . time() . '_' . basename($_FILES['file_buku']['name']);
    $file_path_for_upload = '../' . $file_path_for_db;
    move_uploaded_file($_FILES['file_buku']['tmp_name'], $file_path_for_upload);

    $stmt = $conn->prepare("INSERT INTO buku (judul, penulis, penerbit, tahun_terbit, isbn, kategori, deskripsi, cover_path, file_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssisssss", $judul, $penulis, $penerbit, $tahun_terbit, $isbn, $kategori, $deskripsi, $cover_path_for_db, $file_path_for_db);
    $stmt->execute();
    $stmt->close();

    header("Location: index.php?status=tambah_buku_sukses");
    exit;
}

// Aksi: Menghapus Buku
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['action']) && $_GET['action'] == 'delete_book') {
    $id = $_GET['id'];
    $stmt = $conn->prepare("SELECT cover_path, file_path FROM buku WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    if ($result) {
        if ($result['cover_path'] && file_exists('../' . $result['cover_path'])) {
            unlink('../' . $result['cover_path']);
        }
        if ($result['file_path'] && file_exists('../' . $result['file_path'])) {
            unlink('../' . $result['file_path']);
        }
    }
    $stmt->close();
    $stmt = $conn->prepare("DELETE FROM buku WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: index.php?status=hapus_buku_sukses");
    exit;
}

// Aksi: Menambah Mahasiswa Resmi
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'add_mahasiswa') {
    $nim = $_POST['nim'];
    $nama_lengkap = $_POST['nama_lengkap'];
    if (!empty($nim) && !empty($nama_lengkap)) {
        $stmt = $conn->prepare("INSERT INTO mahasiswa_resmi (nim, nama_lengkap) VALUES (?, ?)");
        $stmt->bind_param("ss", $nim, $nama_lengkap);
        $stmt->execute();
        $stmt->close();
        header("Location: index.php?status=tambah_mhs_sukses");
        exit;
    }
}

// Aksi: Menghapus Mahasiswa Resmi
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['action']) && $_GET['action'] == 'delete_mahasiswa') {
    $nim = $_GET['nim'];
    $stmt = $conn->prepare("DELETE FROM mahasiswa_resmi WHERE nim = ?");
    $stmt->bind_param("s", $nim);
    $stmt->execute();
    $stmt->close();
    header("Location: index.php?status=hapus_mhs_sukses");
    exit;
}

// Aksi: Menghapus Akun Pengguna
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['action']) && $_GET['action'] == 'delete_user') {
    $id = $_GET['id'];
    if ($id != $_SESSION['user_id']) {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: index.php?status=hapus_user_sukses");
    exit;
}

// Aksi: Mengubah Role Pengguna
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'update_role') {
    $id = $_POST['user_id'];
    $role = $_POST['role'];
    if ($id != $_SESSION['user_id']) {
        $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->bind_param("si", $role, $id);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: index.php?status=update_role_sukses");
    exit;
}


// --- PENGAMBILAN DATA UNTUK DITAMPILKAN (TIDAK ADA PERUBAHAN) ---
$total_users = $conn->query("SELECT COUNT(id) as total FROM users")->fetch_assoc()['total'];
$total_mahasiswa_resmi = $conn->query("SELECT COUNT(nim) as total FROM mahasiswa_resmi")->fetch_assoc()['total'];
$total_buku = $conn->query("SELECT COUNT(id) as total FROM buku")->fetch_assoc()['total'];
$buku_result = $conn->query("SELECT id, judul, penulis, cover_path FROM buku ORDER BY id DESC");
$mahasiswa_resmi_result = $conn->query("SELECT nim, nama_lengkap FROM mahasiswa_resmi ORDER BY nim ASC");
$users_result = $conn->query("SELECT id, username, email, nim, role FROM users ORDER BY id ASC");

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background-color: #f0f2f5;
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            width: 260px;
            background: #212529;
            color: white;
            position: fixed;
            height: 100%;
            padding: 1.5rem 1rem;
            flex-shrink: 0;
        }
        .sidebar .nav-link {
            color: #adb5bd;
            font-size: 0.95rem;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: #fff;
            background-color: #495057;
        }
        .sidebar .sidebar-header {
            font-size: 1.5rem;
            font-weight: bold;
            text-align: center;
            margin-bottom: 2rem;
        }
        .main-content {
            margin-left: 260px;
            padding: 2rem;
            width: calc(100% - 260px);
        }
        .stat-card {
            border-radius: 0.75rem;
            padding: 1.5rem;
            display: flex;
            align-items: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-card .icon {
            font-size: 2.5rem;
            padding: 1rem;
            border-radius: 50%;
            margin-right: 1rem;
            background-color: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .card-users {
            background: linear-gradient(135deg, #0d6efd, #0a58ca);
            color: white;
        }
        .card-mahasiswa {
            background: linear-gradient(135deg, #0dcaf0, #0aa3c2);
            color: white;
        }
        .card-buku {
            background: linear-gradient(135deg, #198754, #146c43);
            color: white;
        }
        .stat-card.card-users .text-muted,
        .stat-card.card-mahasiswa .text-muted,
        .stat-card.card-buku .text-muted {
            color: rgba(255, 255, 255, 0.8) !important;
        }

        .stat-card h2 { margin-bottom: 0; }
        .admin-card {
            background-color: #fff;
            border-radius: 0.75rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        .table-custom {
            vertical-align: middle;
        }
        .table-custom img {
            width: 45px;
            height: 60px;
            object-fit: cover;
            border-radius: 0.25rem;
        }
        .btn-icon {
            padding: 0.375rem 0.6rem;
        }
        @media (max-width: 992px) {
            .sidebar {
                position: static;
                width: 100%;
                height: auto;
            }
            .main-content {
                margin-left: 0;
                width: 100%;
            }
            body {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-header">
        <i class="bi bi-person-workspace"></i> Admin
    </div>
    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link active" href="#kelola-buku"><i class="bi bi-book-half me-2"></i> Kelola Buku</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#kelola-mahasiswa"><i class="bi bi-database-gear me-2"></i> Kelola Mahasiswa</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#kelola-akun"><i class="bi bi-person-gear me-2"></i> Kelola Akun</a>
        </li>
        <li class="nav-item mt-3">
            <a class="nav-link" href="../pages/dashboard.php"><i class="bi bi-arrow-left-circle me-2"></i> Kembali ke Situs</a>
        </li>
    </ul>
</div>

<div class="main-content">

    <?php
    if (isset($_GET['status'])) {
        $status = $_GET['status'];
        $message = '';
        $alert_type = 'success';

        switch ($status) {
            case 'edit_sukses':
                $message = '<strong>Berhasil!</strong> Data buku telah berhasil diperbarui.';
                break;
            case 'tambah_buku_sukses':
                $message = '<strong>Berhasil!</strong> Buku baru telah ditambahkan ke koleksi.';
                break;
            case 'hapus_buku_sukses':
                $message = '<strong>Berhasil!</strong> Buku telah dihapus dari koleksi.';
                break;
            case 'tambah_mhs_sukses':
                $message = '<strong>Berhasil!</strong> Mahasiswa resmi telah ditambahkan.';
                break;
            case 'hapus_mhs_sukses':
                $message = '<strong>Berhasil!</strong> Data mahasiswa telah dihapus.';
                break;
            case 'update_role_sukses':
                $message = '<strong>Berhasil!</strong> Peran pengguna telah diperbarui.';
                break;
            case 'hapus_user_sukses':
                $message = '<strong>Berhasil!</strong> Akun pengguna telah dihapus.';
                break;
        }

        if ($message) {
            echo '<div class="alert alert-' . $alert_type . ' alert-dismissible fade show" role="alert">
                    ' . $message . '
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                  </div>';
        }
    }
    ?>

    <h1 class="h2 mb-4">Dashboard Admin</h1>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="stat-card card-users">
                <div class="icon"><i class="bi bi-people-fill"></i></div>
                <div>
                    <h2 class="fw-bold"><?= $total_users ?></h2>
                    <p class="mb-0 text-muted">Total Akun Terdaftar</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card card-mahasiswa">
                <div class="icon"><i class="bi bi-person-badge"></i></div>
                <div>
                    <h2 class="fw-bold"><?= $total_mahasiswa_resmi ?></h2>
                    <p class="mb-0 text-muted">Total Mahasiswa Resmi</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card card-buku">
                <div class="icon"><i class="bi bi-book-half"></i></div>
                <div>
                    <h2 class="fw-bold"><?= $total_buku ?></h2>
                    <p class="mb-0 text-muted">Total Buku</p>
                </div>
            </div>
        </div>
    </div>

    <div class="admin-card mb-4" id="kelola-buku">
        <div class="card-header bg-white py-3">
            <h4 class="mb-0"><i class="bi bi-book-half me-2"></i> Kelola Buku</h4>
        </div>
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
            <div class="table-responsive"><table class="table table-custom table-hover">
                <thead><tr><th>Cover</th><th>Judul</th><th>Penulis</th><th class="text-end">Aksi</th></tr></thead>
                <tbody>
                    <?php while($row = $buku_result->fetch_assoc()): ?>
                    <tr>
                        <td><img src="../<?= htmlspecialchars($row['cover_path']) ?>" alt="cover"></td>
                        <td><?= htmlspecialchars($row['judul']) ?></td>
                        <td><?= htmlspecialchars($row['penulis']) ?></td>
                        <td class="text-end">
                            <a href="edit_buku.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-icon" title="Edit"><i class="bi bi-pencil-square"></i></a>
                            <a href="index.php?action=delete_book&id=<?= $row['id'] ?>" class="btn btn-danger btn-icon" onclick="return confirm('Yakin ingin menghapus buku ini?')" title="Hapus"><i class="bi bi-trash3-fill"></i></a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table></div>
        </div>
    </div>

    <div class="admin-card mb-4" id="kelola-mahasiswa">
        <div class="card-header bg-white py-3">
            <h4 class="mb-0"><i class="bi bi-database-gear me-2"></i> Kelola Mahasiswa Resmi</h4>
        </div>
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
            <div class="table-responsive"><table class="table table-custom table-hover">
                <thead><tr><th>NIM</th><th>Nama Lengkap</th><th class="text-end">Aksi</th></tr></thead>
                <tbody>
                    <?php while($row = $mahasiswa_resmi_result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['nim']) ?></td>
                        <td><?= htmlspecialchars($row['nama_lengkap']) ?></td>
                        <td class="text-end"><a href="index.php?action=delete_mahasiswa&nim=<?= urlencode($row['nim']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin?')">Hapus</a></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table></div>
        </div>
    </div>

    <div class="admin-card" id="kelola-akun">
        <div class="card-header bg-white py-3">
            <h4 class="mb-0"><i class="bi bi-person-gear me-2"></i> Kelola Akun Pengguna</h4>
        </div>
        <div class="card-body p-4">
            <div class="table-responsive"><table class="table table-custom table-hover">
                <thead><tr><th>ID</th><th>Username</th><th>Email</th><th>NIM</th><th>Role</th><th class="text-end">Aksi</th></tr></thead>
                <tbody>
                    <?php while($row = $users_result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['username']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><?= htmlspecialchars($row['nim'] ?? 'N/A') ?></td>
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
                            <a href="index.php?action=delete_user&id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin?')">Hapus</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table></div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
$conn->close();
?>