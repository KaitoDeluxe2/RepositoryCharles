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
  <title>Pengaturan Akun - <?= $current_name ?></title>
  <link href="../css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <style>
    body {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        background-image: url('../Gambar/PerpusGambar.png');
        background-size: cover;
        background-position: center;
    }
    .profile-card {
        background-color: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(15px);
        -webkit-backdrop-filter: blur(15px);
        padding: 2.5rem;
        border-radius: 1rem;
        box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
        border: 1px solid rgba(255, 255, 255, 0.18);
        width: 100%;
        max-width: 500px;
    }
    .profile-avatar {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background-color: #0d6efd;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 3rem;
        font-weight: bold;
        margin: 0 auto 1rem auto;
        box-shadow: 0 4px 15px rgba(13, 110, 253, 0.4);
    }
    .form-control-lg {
        font-size: 1rem;
        padding: 0.75rem 1rem;
    }
    .btn-lg {
        padding: 0.75rem 1rem;
        font-size: 1.05rem;
    }
  </style>
</head>
<body>
  <div class="profile-card">
    <?php if ($message): ?>
      <div class="alert alert-<?= $message_type ?> alert-dismissible fade show"><?= $message ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
    
    <div class="mb-4 text-center">
      <div class="profile-avatar">
          <?= strtoupper(substr($current_name, 0, 1)) ?>
      </div>
      <h2 class="mb-0 fw-bold"><?= $current_name ?></h4>
      <p class="text-muted"><?= $current_email ?></p>
    </div>

    <form action="akun.php" method="POST" autocomplete="off">
      <div class="mb-3">
        <label for="name" class="form-label fw-bold">Nama Pengguna</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
            <input type="text" class="form-control form-control-lg" id="name" name="name" value="<?= $current_name ?>" required>
        </div>
      </div>
      <div class="mb-4">
        <label for="email" class="form-label fw-bold">Alamat Email</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-envelope-fill"></i></span>
            <input type="email" class="form-control form-control-lg" id="email" name="email" value="<?= $current_email ?>" required>
        </div>
      </div>
      <div class="d-grid gap-2">
        <button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-save-fill me-2"></i>Simpan Perubahan</button>
        <a href="dashboard.php" class="btn btn-outline-secondary">Kembali ke Dashboard</a>
      </div>
    </form>
  </div>
  <script src="../js/bootstrap.bundle.min.js"></script>
</body>
</html>