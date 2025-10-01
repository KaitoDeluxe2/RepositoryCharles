<?php
session_start();
include 'includes/db.php';
$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, username, email, password, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];

            if ($_SESSION['role'] === 'admin') {
                header("Location: admin/");
            } else {
                header("Location: pages/dashboard.php");
            }
            exit;
        }
    }
    $error = "Username atau password salah.";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Login - Perpustakaan Digital</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
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
        <h1 class="fw-bold">Selamat Datang Kembali!</h1>
        <p>Login untuk mengakses akun dan fitur perpustakaan digital.</p>
        <img src="Gambar/logo_polibatam_clean_transparent.png" alt="Logo">
    </div>
    <div class="right-panel">
        <div class="form-box">
            <h3 class="fw-bold text-center mb-4">Login ke Akun Anda</h3>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if (isset($_GET['status']) && $_GET['status'] == 'sukses_registrasi'): ?>
                <div class="alert alert-success">Registrasi berhasil! Silakan login.</div>
            <?php endif; ?>
            <form action="login.php" method="POST" autocomplete="off">
                <div class="mb-3">
                    <label>Username</label>
                    <input type="text" name="username" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Login</button>
                <div class="mt-3 text-center"><small>Belum punya akun? <a href="register.html">Daftar di sini</a></small></div>
            </form>
        </div>
    </div>
</div>
</body>
</html>