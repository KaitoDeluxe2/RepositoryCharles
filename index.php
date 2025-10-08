<?php
session_start();

// Cek apakah pengguna sudah login atau belum
if (isset($_SESSION['user_id'])) {
    // Jika sudah login, arahkan ke dashboard
    header("Location: pages/dashboard.php");
    exit();
} else {
    // Jika belum login, arahkan ke halaman login
    header("Location: login.php");
    exit();
}
?>