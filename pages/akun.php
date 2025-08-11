<?php
session_start();
include '../includes/db.php'; // Pastikan path ini benar

// Jika pengguna belum login, arahkan ke halaman login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = "";
$message_type = "success"; // Tipe pesan default

// Logika untuk memproses update profil saat form disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];

    $stmt = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
    $stmt->bind_param("ssi", $name, $email, $user_id);

    if ($stmt->execute()) {
        // Perbarui juga data di session agar langsung tampil di halaman
        $_SESSION['username'] = $name;
        $_SESSION['email'] = $email;
        $message = "Perubahan berhasil disimpan!";
        $message_type = "success";
    } else {
        $message = "Error: Gagal menyimpan perubahan.";
        $message_type = "danger";
    }
    $stmt->close();
}

// Ambil data terbaru dari session untuk ditampilkan
$current_name = htmlspecialchars($_SESSION['username']);
$current_email = htmlspecialchars($_SESSION['email']);
$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Pengaturan Akun - Perpustakaan Digital</title>
  <link href="../css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <style>
    /* Menjadikan body sebagai container utama untuk centering */
    body {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding-top: 56px; /* Memberi ruang untuk navbar jika ada */
        padding-bottom: 20px;
    }
    /* Lapisan latar belakang dengan gambar blur */
    body::before {
        content: '';
        position: fixed;
        top: 0; left: 0;
        width: 100%; height: 100%;
        background-image: url('../Gambar/PerpusGambar.png');
        background-size: cover;
        background-position: center;
        z-index: -1;
        filter: blur(8px);
        -webkit-filter: blur(8px); /* Untuk browser Safari */
    }
    /* Kotak profil dengan efek "glassmorphism" */
    .profile-box {
        background-color: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(10px);
        padding: 2.5rem;
        border-radius: 1rem;
        box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.3);
        width: 100%;
        max-width: 500px; /* Batas lebar maksimum kotak */
    }
  </style>
</head>
<body>
  <div class="profile-box">
    <?php if ($message): ?>
      <div class="alert alert-<?= $message_type ?>"><?= $message ?></div>
    <?php endif; ?>
    
    <div class="mb-4 text-center">
      <i class="bi bi-person-circle fs-1"></i>
      <h2 class="mb-0"><?= $current_name ?></h4>
      <small class="text-muted"><?= $current_email ?></small>
    </div>

    <form action="akun.php" method="POST" autocomplete="off">
      <div class="mb-3">
        <label for="name" class="form-label fw-bold">Nama</label>
        <input type="text" class="form-control form-control-lg" id="name" name="name" value="<?= $current_name ?>" required>
      </div>
      <div class="mb-4">
        <label for="email" class="form-label fw-bold">Email</label>
        <input type="email" class="form-control form-control-lg" id="email" name="email" value="<?= $current_email ?>" required>
      </div>
      <div class="d-grid gap-2">
        <button type="submit" class="btn btn-primary btn-lg">Simpan Perubahan</button>
        <a href="dashboard.php" class="btn btn-secondary">Kembali ke Dashboard</a>
      </div>
    </form>
  </div>
</body>
</html>