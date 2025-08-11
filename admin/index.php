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

// --- LOGIKA AKSI ADMIN ---

// Aksi: Menambah Buku Baru
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'add_book') {
    $judul = $_POST['judul'];
    $penulis = $_POST['penulis'];
    $penerbit = $_POST['penerbit'];
    $tahun_terbit = $_POST['tahun_terbit'];
    $isbn = $_POST['isbn'];
    $kategori = $_POST['kategori'];
    $deskripsi = $_POST['deskripsi'];

    // Menentukan path untuk disimpan di DB (relatif dari root)
    $cover_path_for_db = 'img/covers/' . time() . '_' . basename($_FILES['cover']['name']);
    // Menentukan path untuk fungsi upload (relatif dari file ini)
    $cover_path_for_upload = '../' . $cover_path_for_db;
    move_uploaded_file($_FILES['cover']['tmp_name'], $cover_path_for_upload);

    $file_path_for_db = 'ebooks/' . time() . '_' . basename($_FILES['file_buku']['name']);
    $file_path_for_upload = '../' . $file_path_for_db;
    move_uploaded_file($_FILES['file_buku']['tmp_name'], $file_path_for_upload);

    $stmt = $conn->prepare("INSERT INTO buku (judul, penulis, penerbit, tahun_terbit, isbn, kategori, deskripsi, cover_path, file_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssisssss", $judul, $penulis, $penerbit, $tahun_terbit, $isbn, $kategori, $deskripsi, $cover_path_for_db, $file_path_for_db);
    $stmt->execute();
    $stmt->close();

    header("Location: index.php");
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
        // [PATH DIPERBAIKI] - Gunakan ../ untuk menghapus file fisik
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
    header("Location: index.php");
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
        header("Location: index.php");
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
    header("Location: index.php");
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
    header("Location: index.php");
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
    header("Location: index.php");
    exit;
}


// --- PENGAMBILAN DATA UNTUK DITAMPILKAN ---
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
</head>
<body class="bg-light">

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2">Admin Dashboard</h1>
        <a href="../pages/dashboard.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Kembali ke Dashboard User</a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4"><div class="card text-bg-primary"><div class="card-body"><h5 class="card-title"><?= $total_users ?></h5><p class="card-text">Total Akun Terdaftar</p></div></div></div>
        <div class="col-md-4"><div class="card text-bg-info"><div class="card-body"><h5 class="card-title"><?= $total_mahasiswa_resmi ?></h5><p class="card-text">Total Mahasiswa Resmi</p></div></div></div>
        <div class="col-md-4"><div class="card text-bg-success"><div class="card-body"><h5 class="card-title"><?= $total_buku ?></h5><p class="card-text">Total Buku</p></div></div></div>
    </div>

    <div class="card mb-4">
        <div class="card-header"><h4><i class="bi bi-book-half"></i> Kelola Buku</h4></div>
        <div class="card-body">
            <h5>Tambah Buku Baru</h5>
            <form id="form-tambah-buku" action="index.php" method="POST" enctype="multipart/form-data" class="mb-4">
                <input type="hidden" name="action" value="add_book">
                <div class="row"><div class="col-md-8 mb-3"><label class="form-label">Judul Buku</label><input type="text" name="judul" class="form-control" required></div><div class="col-md-4 mb-3"><label class="form-label">Penulis</label><input type="text" name="penulis" class="form-control" required></div></div>
                <div class="row"><div class="col-md-4 mb-3"><label class="form-label">Penerbit</label><input type="text" name="penerbit" class="form-control"></div><div class="col-md-2 mb-3"><label class="form-label">Tahun Terbit</label><input type="number" name="tahun_terbit" class="form-control" placeholder="Contoh: 2023"></div><div class="col-md-3 mb-3"><label class="form-label">ISBN</label><input type="text" name="isbn" class="form-control"></div><div class="col-md-3 mb-3"><label class="form-label">Kategori</label><input type="text" name="kategori" class="form-control"></div></div>
                <div class="mb-3"><label class="form-label">Deskripsi Singkat</label><textarea name="deskripsi" class="form-control" rows="3"></textarea></div>
                <div class="row"><div class="col-md-6 mb-3"><label class="form-label">File Cover (Gambar)</label><input type="file" id="cover-input" name="cover" class="form-control" accept="image/*" required></div><div class="col-md-6 mb-3"><label class="form-label">File Buku (PDF)</label><input type="file" id="file-buku-input" name="file_buku" class="form-control" accept=".pdf" required></div></div>
                <button type="submit" class="btn btn-primary w-100">Tambah Buku</button>
            </form>
            <hr>
            <h5>Daftar Buku di Perpustakaan</h5>
            <div class="table-responsive"><table class="table table-striped table-hover">
                <thead><tr><th>Cover</th><th>Judul</th><th>Penulis</th><th>Aksi</th></tr></thead>
                <tbody>
                    <?php while($row = $buku_result->fetch_assoc()): ?>
                    <tr>
                        <td><img src="../<?= htmlspecialchars($row['cover_path']) ?>" alt="cover" width="50" class="img-thumbnail"></td>
                        <td><?= htmlspecialchars($row['judul']) ?></td>
                        <td><?= htmlspecialchars($row['penulis']) ?></td>
                        <td>
                            <a href="edit_buku.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="index.php?action=delete_book&id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus buku ini?')">Hapus</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table></div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header"><h4><i class="bi bi-database-gear"></i> Kelola Mahasiswa Resmi</h4></div>
        <div class="card-body">
            <h5>Tambah Mahasiswa Baru</h5>
            <form action="index.php" method="POST" class="mb-4">
                <input type="hidden" name="action" value="add_mahasiswa">
                <div class="row g-2">
                    <div class="col-md-5"><input type="text" name="nim" class="form-control" placeholder="NIM" required></div>
                    <div class="col-md-5"><input type="text" name="nama_lengkap" class="form-control" placeholder="Nama Lengkap" required></div>
                    <div class="col-md-2"><button type="submit" class="btn btn-primary w-100">Tambah</button></div>
                </div>
            </form>
            <hr>
            <h5>Daftar Mahasiswa Resmi</h5>
            <div class="table-responsive"><table class="table table-striped table-hover">
                <thead><tr><th>NIM</th><th>Nama Lengkap</th><th>Aksi</th></tr></thead>
                <tbody>
                    <?php while($row = $mahasiswa_resmi_result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['nim']) ?></td>
                        <td><?= htmlspecialchars($row['nama_lengkap']) ?></td>
                        <td><a href="index.php?action=delete_mahasiswa&nim=<?= urlencode($row['nim']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin?')">Hapus</a></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table></div>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h4><i class="bi bi-person-gear"></i> Kelola Akun Pengguna</h4></div>
        <div class="card-body">
            <div class="table-responsive"><table class="table table-striped table-hover">
                <thead><tr><th>ID</th><th>Username</th><th>Email</th><th>NIM</th><th>Role</th><th>Aksi</th></tr></thead>
                <tbody>
                    <?php while($row = $users_result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['username']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><?= htmlspecialchars($row['nim'] ?? 'N/A') ?></td>
                        <td>
                            <form action="index.php" method="POST" class="d-flex">
                                <input type="hidden" name="action" value="update_role">
                                <input type="hidden" name="user_id" value="<?= $row['id'] ?>">
                                <select name="role" class="form-select form-select-sm" onchange="this.form.submit()" <?= ($row['id'] == $_SESSION['user_id']) ? 'disabled' : '' ?>>
                                    <option value="user" <?= ($row['role'] == 'user') ? 'selected' : '' ?>>User</option>
                                    <option value="admin" <?= ($row['role'] == 'admin') ? 'selected' : '' ?>>Admin</option>
                                </select>
                            </form>
                        </td>
                        <td>
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

<script>
    // Kode JavaScript validasi file tidak perlu diubah
</script>

</body>
</html>
<?php
$conn->close();
?>