<?php
session_start();

// Cek apakah pengguna sudah login
if (isset($_SESSION['user_id'])) {
    
    // Jika sudah login, cek rolenya
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        // Jika dia adalah admin, arahkan ke dashboard admin
        header("Location: admin/");
    } else {
        // Jika dia adalah user biasa, arahkan ke dashboard user
        header("Location: pages/dashboard.php");
    }

} else {
    // Jika belum login sama sekali, arahkan ke halaman login
    header("Location: login.php");
}
exit;
?>