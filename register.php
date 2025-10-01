<?php
// Dua baris ini untuk menampilkan error yang tersembunyi
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Hanya proses jika form di-submit (metode POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Pastikan path ini benar sesuai struktur folder Anda
    include 'includes/db.php';

    $email = $_POST['email'];
    $nim = $_POST['nim'];
    $username = $_POST['username'];
    $password = $_POST['password'];

    // 1. Validasi input: semua field wajib diisi
    if (empty($email) || empty($nim) || empty($username) || empty($password)) {
        die("Error: Semua field (Email, NIM, Username, Password) wajib diisi.");
    }

    // 2. Validasi ke data induk: Cek apakah NIM terdaftar secara resmi
    $stmt = $conn->prepare("SELECT nim FROM mahasiswa_resmi WHERE nim = ?");
    $stmt->bind_param("s", $nim);
    $stmt->execute();
    if ($stmt->get_result()->num_rows === 0) {
        die("Error: NIM Anda tidak terdaftar secara resmi. Hubungi bagian akademik.");
    }
    $stmt->close();

    // 3. Validasi keunikan: Cek apakah NIM atau Email sudah dipakai untuk membuat akun
    $stmt = $conn->prepare("SELECT id FROM users WHERE nim = ? OR email = ?");
    $stmt->bind_param("ss", $nim, $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        die("Error: Akun untuk NIM atau Email ini sudah pernah dibuat.");
    }
    $stmt->close();

    // 4. Proses Simpan Data
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Semua yang mendaftar di sini PASTI 'user' dan punya NIM
    $stmt = $conn->prepare("INSERT INTO users (username, email, nim, password, role) VALUES (?, ?, ?, ?, 'user')");
    $stmt->bind_param("ssss", $username, $email, $nim, $hashed_password);

    if ($stmt->execute()) {
        header("Location: login.php?status=sukses_registrasi");
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();

} else {
    // Jika diakses langsung, redirect kembali ke form registrasi
    header("Location: register.html");
    exit;
}
?>
