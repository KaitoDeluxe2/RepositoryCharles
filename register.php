<?php
// Variabel untuk menampung pesan error
$error = "";

// Hanya proses jika form di-submit (metode POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Sertakan koneksi database
    include 'includes/db.php';

    $email = $_POST['email'];
    $nim = $_POST['nim'];
    $username = $_POST['username'];
    $password = $_POST['password'];

    // 1. Validasi input: semua field wajib diisi
    if (empty($email) || empty($nim) || empty($username) || empty($password)) {
        $error = "Semua field (Email, NIM, Username, Password) wajib diisi.";
    } else {
        // 2. Validasi ke data induk: Cek apakah NIM terdaftar secara resmi
        $stmt = $conn->prepare("SELECT nim FROM mahasiswa_resmi WHERE nim = ?");
        $stmt->bind_param("s", $nim);
        $stmt->execute();
        if ($stmt->get_result()->num_rows === 0) {
            $error = "NIM Anda tidak terdaftar secara resmi. Hubungi bagian akademik.";
        }
        $stmt->close();

        // 3. Validasi keunikan (jika tidak ada error sebelumnya)
        if (empty($error)) {
            $stmt = $conn->prepare("SELECT id FROM users WHERE nim = ? OR email = ? OR username = ?");
            $stmt->bind_param("sss", $nim, $email, $username);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                $error = "Akun untuk NIM, Email, atau Username ini sudah pernah dibuat.";
            }
            $stmt->close();
        }

        // 4. Proses Simpan Data (jika tidak ada error sama sekali)
        if (empty($error)) {
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            
            // Membuat seed unik untuk avatar
            $avatar_seed = hash('sha256', $username . time());

            // Tambahkan kolom avatar_seed ke query INSERT
            $stmt = $conn->prepare("INSERT INTO users (username, email, nim, password, role, avatar_seed) VALUES (?, ?, ?, ?, 'user', ?)");
            $stmt->bind_param("sssss", $username, $email, $nim, $hashed_password, $avatar_seed);

            if ($stmt->execute()) {
                // Jika berhasil, redirect ke halaman login dengan status sukses
                header("Location: login.php?status=sukses_registrasi");
                exit;
            } else {
                $error = "Terjadi kesalahan pada server. Silakan coba lagi nanti.";
            }
            $stmt->close();
        }
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Register - Perpustakaan Digital</title>
  <link href="css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { font-family: sans-serif; margin: 0; background-color: #f8f9fa; }
    .container-wrapper { display: flex; min-height: 100vh; }
    .left-panel { flex: 1; background: linear-gradient(135deg, #2b0d3a, #140d2a); color: white; display: flex; flex-direction: column; justify-content: center; align-items: center; padding: 2rem; text-align: center; }
    .left-panel img { max-width: 240px; margin-top: 1.5rem; }
    .right-panel { flex: 1; display: flex; align-items: center; justify-content: center; padding: 2rem; }
    .form-box { width: 100%; max-width: 400px; }
    @media (max-width: 768px) { .container-wrapper { flex-direction: column; } }
  </style>
</head>
<body>
<div class="container-wrapper">
    <div class="left-panel">
        <h1 class="fw-bold">Buat Akun Gratis</h1>
        <p>Gabung dan nikmati semua fitur perpustakaan digital.</p>
        <img src="Gambar/logo_polibatam_clean_transparent.png" alt="Logo">
    </div>
    <div class="right-panel">
        <div class="form-box">
            <h3 class="fw-bold text-center mb-4">Form Registrasi</h3>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form action="register.php" method="POST" autocomplete="off">
              <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" id="email" name="email" class="form-control" required value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
              </div>
              <div class="mb-3">
                <label for="nim" class="form-label">NIM (Nomor Induk Mahasiswa)</label>
                <input type="text" id="nim" name="nim" class="form-control" required value="<?= isset($_POST['nim']) ? htmlspecialchars($_POST['nim']) : '' ?>">
              </div>
              <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" id="username" name="username" class="form-control" required value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>">
              </div>
              <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" id="password" name="password" class="form-control" required>
              </div>
              <button type="submit" class="btn btn-primary w-100">Daftar</button>
              <div class="text-center mt-3">
                <small>Sudah punya akun? <a href="login.php">Login di sini</a></small>
              </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>