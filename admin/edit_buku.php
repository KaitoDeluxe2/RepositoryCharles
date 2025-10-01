<?php
session_start();
include '../includes/db.php';

// Keamanan: Pastikan hanya admin yang bisa mengakses
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$message = "";

// --- BAGIAN 1: MEMPROSES FORM SAAT DISUBMIT (UPDATE DATA) - TIDAK ADA PERUBAHAN ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    $id = $_POST['id'];
    $judul = $_POST['judul'];
    $penulis = $_POST['penulis'];
    $penerbit = $_POST['penerbit'];
    $tahun_terbit = $_POST['tahun_terbit'];
    $isbn = $_POST['isbn'];
    $kategori = $_POST['kategori'];
    $deskripsi = $_POST['deskripsi'];
    $old_cover_path = $_POST['old_cover_path'];
    $old_file_path = $_POST['old_file_path'];

    $cover_path_for_db = $old_cover_path;
    $file_path_for_db = $old_file_path;

    // Cek dan proses jika ada file cover baru
    if (isset($_FILES['cover']) && $_FILES['cover']['error'] == 0 && !empty($_FILES['cover']['name'])) {
        if (!empty($old_cover_path) && file_exists('../' . $old_cover_path)) {
            unlink('../' . $old_cover_path);
        }
        $cover_name = time() . '_' . basename($_FILES['cover']['name']);
        $cover_path_for_db = 'Gambar/Covers/' . $cover_name;
        $cover_path_for_upload = '../' . $cover_path_for_db;
        move_uploaded_file($_FILES['cover']['tmp_name'], $cover_path_for_upload);
    }

    // Cek dan proses jika ada file PDF baru
    if (isset($_FILES['file_buku']) && $_FILES['file_buku']['error'] == 0 && !empty($_FILES['file_buku']['name'])) {
        if (!empty($old_file_path) && file_exists('../' . $old_file_path)) {
            unlink('../' . $old_file_path);
        }
        $file_name = time() . '_' . basename($_FILES['file_buku']['name']);
        $file_path_for_db = 'ebooks/' . $file_name;
        $file_path_for_upload = '../' . $file_path_for_db;
        if (!move_uploaded_file($_FILES['file_buku']['tmp_name'], $file_path_for_upload)) {
             die("Error: Gagal memindahkan file PDF. Pastikan folder ebooks/ bisa ditulisi (writable).");
        }
    }

    // Query UPDATE
    $stmt = $conn->prepare("UPDATE buku SET judul=?, penulis=?, penerbit=?, tahun_terbit=?, isbn=?, kategori=?, deskripsi=?, cover_path=?, file_path=? WHERE id=?");
    $stmt->bind_param("sssisssssi", $judul, $penulis, $penerbit, $tahun_terbit, $isbn, $kategori, $deskripsi, $cover_path_for_db, $file_path_for_db, $id);
    
    if ($stmt->execute()) {
        header("Location: index.php?status=edit_sukses");
        exit;
    } else {
        $message = "Error: Gagal memperbarui data.";
    }
    $stmt->close();
}


// --- BAGIAN 2: MENGAMBIL DATA UNTUK DITAMPILKAN DI FORM - TIDAK ADA PERUBAHAN ---
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}
$book_id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM buku WHERE id = ?");
$stmt->bind_param("i", $book_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo "Buku tidak ditemukan.";
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
    <title>Edit Buku: <?= htmlspecialchars($book['judul']) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background-color: #f0f2f5;
        }
        .edit-container {
            background-color: #ffffff;
            border-radius: 1rem;
            box-shadow: 0 8px 32px 0 rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .cover-preview-section {
            background: linear-gradient(145deg, #2c3e50, #34495e);
            color: white;
            padding: 2.5rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
        }
        .cover-preview-img {
            width: 200px;
            height: 280px;
            object-fit: cover;
            border-radius: 0.5rem;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            margin-bottom: 1.5rem;
            border: 3px solid white;
        }
        .form-section {
            padding: 2.5rem;
        }
        .form-section h2 {
            font-weight: 700;
        }
        .form-label {
            font-weight: 600;
            color: #495057;
        }
        .form-control, .form-control:focus {
            background-color: #f8f9fa;
            border-color: #dee2e6;
        }
        .form-control:focus {
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }
        .upload-info {
            background-color: #e9ecef;
            padding: 0.75rem;
            border-radius: 0.5rem;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="edit-container col-lg-10 mx-auto">
        <div class="row g-0">
            <div class="col-lg-4 cover-preview-section">
                <h4 class="mb-3">Pratinjau Sampul</h4>
                <img src="../<?= htmlspecialchars($book['cover_path']) ?>" alt="Cover Buku" class="cover-preview-img">
                <p class="mb-1 fw-bold"><?= htmlspecialchars($book['judul']) ?></p>
                <p class="small text-white-50">oleh <?= htmlspecialchars($book['penulis']) ?></p>
            </div>

            <div class="col-lg-8 form-section">
                <h2 class="mb-4">Edit Detail Buku</h2>
                
                <?php if (!empty($message)): ?>
                    <div class="alert alert-danger"><?= $message ?></div>
                <?php endif; ?>

                <form action="edit_buku.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?= $book['id'] ?>">
                    <input type="hidden" name="old_cover_path" value="<?= htmlspecialchars($book['cover_path']) ?>">
                    <input type="hidden" name="old_file_path" value="<?= htmlspecialchars($book['file_path']) ?>">

                    <div class="row g-3 mb-3">
                        <div class="col-md-8"><label class="form-label">Judul Buku</label><input type="text" name="judul" class="form-control" value="<?= htmlspecialchars($book['judul']) ?>" required></div>
                        <div class="col-md-4"><label class="form-label">Penulis</label><input type="text" name="penulis" class="form-control" value="<?= htmlspecialchars($book['penulis']) ?>" required></div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-4"><label class="form-label">Penerbit</label><input type="text" name="penerbit" class="form-control" value="<?= htmlspecialchars($book['penerbit']) ?>"></div>
                        <div class="col-md-2"><label class="form-label">Tahun</label><input type="number" name="tahun_terbit" class="form-control" value="<?= htmlspecialchars($book['tahun_terbit']) ?>"></div>
                        <div class="col-md-3"><label class="form-label">ISBN</label><input type="text" name="isbn" class="form-control" value="<?= htmlspecialchars($book['isbn']) ?>"></div>
                        <div class="col-md-3"><label class="form-label">Kategori</label><input type="text" name="kategori" class="form-control" value="<?= htmlspecialchars($book['kategori']) ?>"></div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="deskripsi" class="form-control" rows="4"><?= htmlspecialchars($book['deskripsi']) ?></textarea>
                    </div>
                    
                    <h5 class="mb-3">Perbarui File</h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Ganti Cover (Opsional)</label>
                            <input type="file" name="cover" class="form-control" accept="image/*">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ganti File PDF (Opsional)</label>
                            <input type="file" name="file_buku" class="form-control" accept=".pdf">
                            <div class="form-text">File saat ini: <?= basename(htmlspecialchars($book['file_path'])) ?></div>
                        </div>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-5">
                        <a href="index.php" class="btn btn-outline-secondary px-4">Batal</a>
                        <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save-fill me-2"></i>Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

</body>
</html>