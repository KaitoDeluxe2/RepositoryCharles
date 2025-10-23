<?php
 // File: pages/get_user_preview.php
 
 session_start(); // Mulai session jika belum
 include '../includes/db.php'; // Sesuaikan path ke file koneksi database Anda
 
 header('Content-Type: application/json'); // Set header agar output dikenali sebagai JSON
 
 // Inisialisasi array response default
 $response = ['success' => false, 'message' => 'Permintaan tidak valid.'];
 
 // 1. Validasi Input: Pastikan user_id ada dan berupa angka
 if (!isset($_GET['user_id']) || !is_numeric($_GET['user_id'])) {
     echo json_encode($response);
     exit;
 }
 
 $preview_user_id = (int)$_GET['user_id'];
 
 // Jika user_id valid, coba ambil data
 try {
     // 2. Ambil Data Dasar Pengguna (Username, Avatar Seed, Bio) dari tabel 'users'
     // Pastikan tabel 'users' sudah punya kolom 'bio' (TEXT NULL)
     $stmt_user = $conn->prepare("SELECT username, avatar_seed, bio FROM users WHERE id = ?");
     if ($stmt_user === false) {
         throw new Exception("Gagal menyiapkan statement user: " . $conn->error);
     }
     $stmt_user->bind_param("i", $preview_user_id);
     $stmt_user->execute();
     $result_user = $stmt_user->get_result();
 
     if ($result_user->num_rows > 0) {
         $user_data = $result_user->fetch_assoc();
 
         // 3. Hitung Jumlah Total Diskusi (komentar) dari tabel 'diskusi'
         $stmt_diskusi = $conn->prepare("SELECT COUNT(id) as total_diskusi FROM diskusi WHERE user_id = ?");
         if ($stmt_diskusi === false) {
             throw new Exception("Gagal menyiapkan statement diskusi: " . $conn->error);
         }
         $stmt_diskusi->bind_param("i", $preview_user_id);
         $stmt_diskusi->execute();
         // Ambil hasil, default 0 jika tidak ada hasil
         $total_diskusi = $stmt_diskusi->get_result()->fetch_assoc()['total_diskusi'] ?? 0;
         $stmt_diskusi->close();
 
         // 4. Hitung Jumlah Total Suka yang Diterima (SUM dari 'likes' di tabel 'diskusi')
         $stmt_likes = $conn->prepare("SELECT SUM(likes) as total_suka FROM diskusi WHERE user_id = ?");
          if ($stmt_likes === false) {
             throw new Exception("Gagal menyiapkan statement likes: " . $conn->error);
         }
         $stmt_likes->bind_param("i", $preview_user_id);
         $stmt_likes->execute();
         // Ambil hasil, default 0 jika NULL (belum pernah dapat like)
         $total_suka = $stmt_likes->get_result()->fetch_assoc()['total_suka'] ?? 0;
         $stmt_likes->close();
 
         // 5. Siapkan Data untuk JSON Response
         $response['success'] = true;
         $response['message'] = 'Data user berhasil diambil.'; // Pesan sukses
         $response['user'] = [
             'username' => htmlspecialchars($user_data['username']),
             // Buat URL avatar, gunakan username sebagai fallback jika seed kosong
             'avatarUrl' => "https://api.dicebear.com/8.x/croodles/svg?seed=" . urlencode($user_data['avatar_seed'] ?: $user_data['username']) . "&backgroundColor=0d6efd",
             // Tampilkan bio atau pesan default jika kosong
             'bio' => !empty(trim($user_data['bio'])) ? htmlspecialchars($user_data['bio']) : 'Pengguna ini belum menulis bio.',
             'total_diskusi' => (int)$total_diskusi, // Pastikan integer
             'total_suka' => (int)$total_suka     // Pastikan integer
         ];
 
     } else {
         // Jika user_id tidak ditemukan di database
         $response['message'] = 'User tidak ditemukan.';
     }
 
     $stmt_user->close();
 
 } catch (mysqli_sql_exception $e) {
     // Tangani error SQL
     error_log("SQL Error in get_user_preview.php: " . $e->getMessage()); // Log error ke server log
     $response['message'] = 'Terjadi kesalahan database.';
     // Jangan tampilkan detail error SQL ke pengguna
 
 } catch (Exception $e) {
     // Tangani error umum lainnya
     error_log("General Error in get_user_preview.php: " . $e->getMessage()); // Log error
     $response['message'] = 'Terjadi kesalahan pada server.';
 }
 
 // Tutup koneksi database
 $conn->close();
 
 // Cetak hasil dalam format JSON
 echo json_encode($response);
 exit; // Akhiri script
 ?>