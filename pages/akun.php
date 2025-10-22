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
 $message_type = "success"; // Default message type
 $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'activity'; // Default tab adalah 'activity'
 
 // --- LOGIKA UPDATE PROFIL ---
 if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'update_profile') {
     $name = trim($_POST['name']);
     $email = trim($_POST['email']);
     // Ambil bio dari form, default string kosong jika tidak ada atau null
     $bio = trim($_POST['bio'] ?? '');
 
     // Validasi dasar: Nama dan Email tidak boleh kosong
     if (!empty($name) && !empty($email)) {
         // Cek duplikasi email (opsional, jika email harus unik antar user lain)
         $stmt_check = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
         $stmt_check->bind_param("si", $email, $user_id);
         $stmt_check->execute();
         $result_check = $stmt_check->get_result();
 
         if ($result_check->num_rows > 0) {
             $message = "Error: Email tersebut sudah digunakan oleh akun lain.";
             $message_type = "danger";
             $active_tab = 'settings'; // Jaga user tetap di tab settings jika ada error
         } else {
             // Update username, email, dan bio di database
             // Pastikan kolom 'bio' sudah ada di tabel 'users'. Jika belum, jalankan:
             // ALTER TABLE users ADD bio TEXT NULL DEFAULT NULL AFTER avatar_seed;
             $stmt_update = $conn->prepare("UPDATE users SET username = ?, email = ?, bio = ? WHERE id = ?");
             $stmt_update->bind_param("sssi", $name, $email, $bio, $user_id);
 
             if ($stmt_update->execute()) {
                 $_SESSION['username'] = $name; // Update nama di session agar langsung berubah di tampilan
                 // Update email di session jika perlu: $_SESSION['email'] = $email;
                 $message = "Perubahan profil berhasil disimpan!";
                 $message_type = "success";
                 $active_tab = 'settings'; // Jaga user di tab settings setelah sukses update
             } else {
                 $message = "Error: Gagal menyimpan perubahan profil ke database.";
                 $message_type = "danger";
                 $active_tab = 'settings'; // Jaga user di tab settings jika gagal update
             }
             $stmt_update->close();
         }
         $stmt_check->close();
     } else {
         $message = "Error: Nama Pengguna dan Email tidak boleh kosong.";
         $message_type = "danger";
         $active_tab = 'settings'; // Jaga user di tab settings jika validasi gagal
     }
 }
 
 // --- AMBIL DATA PENGGUNA TERBARU & STATISTIK ---
 // Ambil data lengkap pengguna dari database, TERMASUK BIO dan tanggal bergabung
 $stmt_user = $conn->prepare("SELECT username, email, nim, role, avatar_seed, bergabung_sejak, bio FROM users WHERE id = ?");
 if ($stmt_user === false) {
      die("Prepare failed: (" . $conn->errno . ") " . $conn->error); // Error handling
 }
 $stmt_user->bind_param("i", $user_id);
 $stmt_user->execute();
 $user_result = $stmt_user->get_result();
 if ($user_result->num_rows === 0) {
     // Handle jika user tidak ditemukan (meskipun seharusnya tidak terjadi jika sudah login)
     session_destroy(); // Logout paksa
     header("Location: ../login.php?error=user_not_found");
     exit;
 }
 $user_data = $user_result->fetch_assoc();
 $stmt_user->close();
 
 // Sanitasi data untuk ditampilkan di HTML
 $current_name = htmlspecialchars($user_data['username']);
 $current_email = htmlspecialchars($user_data['email']);
 $current_nim = htmlspecialchars($user_data['nim'] ?? 'N/A'); // Handle jika NIM null
 $current_role = $user_data['role']; // Tidak perlu htmlspecialchars untuk role internal
 $avatar_seed = $user_data['avatar_seed']; // Seed tidak perlu htmlspecialchars jika hanya untuk URL
 $tanggal_bergabung = date('d M Y', strtotime($user_data['bergabung_sejak'])); // Format tanggal
 $current_bio = htmlspecialchars($user_data['bio'] ?? ''); // Ambil bio, default string kosong
 
 // Statistik: Hitung jumlah total komentar
 $stmt_diskusi = $conn->prepare("SELECT COUNT(id) as total_diskusi FROM diskusi WHERE user_id = ?");
 $stmt_diskusi->bind_param("i", $user_id);
 $stmt_diskusi->execute();
 $total_diskusi = $stmt_diskusi->get_result()->fetch_assoc()['total_diskusi'];
 $stmt_diskusi->close();
 
 // Statistik: Hitung jumlah total suka yang diterima dari komentar pengguna
 $stmt_likes = $conn->prepare("SELECT SUM(likes) as total_suka FROM diskusi WHERE user_id = ?");
 $stmt_likes->bind_param("i", $user_id);
 $stmt_likes->execute();
 $total_suka = $stmt_likes->get_result()->fetch_assoc()['total_suka'] ?? 0; // Default ke 0 jika belum pernah ada like
 $stmt_likes->close();
 
 // --- LOGIKA PAGINASI UNTUK AKTIVITAS ---
 $item_per_halaman = 5; // Jumlah aktivitas per halaman
 $halaman_aktif = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int)$_GET['page']) : 1; // Pastikan halaman >= 1
 $offset = ($halaman_aktif - 1) * $item_per_halaman;
 
 // Hitung total halaman hanya jika ada diskusi
 $total_halaman = ($total_diskusi > 0) ? ceil($total_diskusi / $item_per_halaman) : 0;
 
 // Pastikan halaman aktif tidak melebihi total halaman
 if ($halaman_aktif > $total_halaman && $total_halaman > 0) {
      $halaman_aktif = $total_halaman;
      $offset = ($halaman_aktif - 1) * $item_per_halaman; // Hitung ulang offset
 }
 
 // Aktivitas: Ambil komentar terakhir dari pengguna sesuai halaman
 $activities = null; // Inisialisasi $activities
 if ($total_diskusi > 0) {
     $stmt_activity = $conn->prepare(
         "SELECT d.komentar, d.tanggal, b.judul as judul_buku, b.id as buku_id 
          FROM diskusi d 
          JOIN buku b ON d.buku_id = b.id 
          WHERE d.user_id = ? 
          ORDER BY d.tanggal DESC 
          LIMIT ? OFFSET ?"
     );
     $stmt_activity->bind_param("iii", $user_id, $item_per_halaman, $offset);
     $stmt_activity->execute();
     $activities = $stmt_activity->get_result(); // Simpan hasil query
     $stmt_activity->close();
 }
 
 $conn->close(); // Tutup koneksi database
 ?>
 <!DOCTYPE html>
 <html lang="id">
 <head>
     <meta charset="UTF-8" />
     <meta name="viewport" content="width=device-width, initial-scale=1.0" />
     <title>Profil <?= $current_name ?></title>
     <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
     <script src="https://cdn.tailwindcss.com"></script>
     <script>
         // Konfigurasi Tailwind (dari index.html contoh)
         tailwind.config = {
             darkMode: 'class', // Mengaktifkan dark mode via class 'dark'
             theme: {
                 extend: {
                     colors: {
                         'primary': { // Warna primer (biru)
                             '50': '#eff6ff', '100': '#dbeafe', '200': '#bfdbfe',
                             '300': '#93c5fd', '400': '#60a5fa', '500': '#3b82f6',
                             '600': '#2563eb', '700': '#1d4ed8', '800': '#1e40af',
                             '900': '#1e3a8a', '950': '#172554',
                         },
                     }
                 }
             }
         }
 
         // Script Pembaca Tema dari LocalStorage (Jalankan SEGERA di <head>)
         (function() {
             const theme = localStorage.getItem('theme') || 'light'; // Default ke light
             if (theme === 'dark') {
                 document.documentElement.classList.add('dark'); // Gunakan class 'dark' untuk Tailwind
             } else {
                 document.documentElement.classList.remove('dark');
             }
         })();
     </script>
     <style>
         /* Style tambahan jika diperlukan */
         /* Menyembunyikan tab content yang tidak aktif */
         .tab-content > div:not(.active) {
             display: none;
         }
     </style>
 </head>
 <body class="antialiased bg-slate-100 dark:bg-slate-900 text-slate-800 dark:text-slate-200 transition-colors duration-300">
 
 <div class="container mx-auto p-4 sm:p-6 lg:p-8 max-w-5xl">
     <header class="mb-6">
         <a href="dashboard.php" class="inline-flex items-center text-sm font-semibold text-slate-600 dark:text-slate-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
             <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                 <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
             </svg>
             Kembali ke Dashboard
         </a>
     </header>
 
     <main>
         <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-lg p-6 md:p-8 transition-colors duration-300 mb-8">
             <div class="flex flex-col md:flex-row items-center md:items-start text-center md:text-left">
                 <img
                   src="https://api.dicebear.com/8.x/croodles/svg?seed=<?= urlencode($avatar_seed) ?>"
                   alt="Avatar <?= $current_name ?>"
                   class="w-28 h-28 rounded-full border-4 border-slate-200 dark:border-slate-700 shadow-md flex-shrink-0"
                 />
                 <div class="md:ml-8 mt-4 md:mt-0 flex-grow">
                   <h1 class="text-3xl md:text-4xl font-extrabold text-slate-900 dark:text-white"><?= $current_name ?></h1>
                   <p class="text-slate-500 dark:text-slate-400 mt-1"><?= $current_email ?></p>
                   <?php if ($current_role !== 'admin' && $current_nim !== 'N/A'): ?>
                     <p class="text-slate-500 dark:text-slate-400 text-sm">NIM: <?= $current_nim ?></p>
                   <?php endif; ?>
                   <?php if (!empty($current_bio)): ?>
                     <p class="mt-3 text-sm text-slate-600 dark:text-slate-300 italic">"<?= $current_bio ?>"</p>
                   <?php endif; ?>
                 </div>
               </div>
               <div class="border-t border-slate-200 dark:border-slate-700 mt-6 pt-6">
                 <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 text-center">
                   <div class="flex flex-col items-center p-4 rounded-lg bg-slate-50 dark:bg-slate-700/50">
                     <div class="mb-2 text-primary-500">
                       <i class="bi bi-chat-dots-fill text-2xl"></i>
                     </div>
                     <p class="text-2xl font-bold text-slate-900 dark:text-white"><?= $total_diskusi ?></p>
                     <p class="text-sm text-slate-500 dark:text-slate-400">Diskusi</p>
                   </div>
                   <div class="flex flex-col items-center p-4 rounded-lg bg-slate-50 dark:bg-slate-700/50">
                     <div class="mb-2 text-pink-500">
                         <i class="bi bi-heart-fill text-2xl"></i>
                     </div>
                     <p class="text-2xl font-bold text-slate-900 dark:text-white"><?= $total_suka ?></p>
                     <p class="text-sm text-slate-500 dark:text-slate-400">Total Suka Diterima</p>
                   </div>
                   <div class="flex flex-col items-center p-4 rounded-lg bg-slate-50 dark:bg-slate-700/50">
                     <div class="mb-2 text-green-500">
                         <i class="bi bi-calendar-check-fill text-2xl"></i>
                     </div>
                     <p class="text-xl font-bold text-slate-900 dark:text-white"><?= $tanggal_bergabung ?></p>
                     <p class="text-sm text-slate-500 dark:text-slate-400">Tanggal Bergabung</p>
                   </div>
                 </div>
               </div>
         </div>
         <div class="border-b border-slate-200 dark:border-slate-700 mb-8">
             <nav class="-mb-px flex space-x-6" aria-label="Tabs">
                 <button data-tab="activity"
                         class="tab-button whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-all duration-200 <?= ($active_tab == 'activity') ? 'border-primary-500 text-primary-600 dark:text-primary-400' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:border-slate-600' ?>">
                     <i class="bi bi-list-ul mr-1"></i> Aktivitas Diskusi
                 </button>
                 <button data-tab="settings"
                         class="tab-button whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-all duration-200 <?= ($active_tab == 'settings') ? 'border-primary-500 text-primary-600 dark:text-primary-400' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:border-slate-600' ?>">
                     <i class="bi bi-gear-fill mr-1"></i> Pengaturan Akun
                 </button>
             </nav>
         </div>
         <div class="tab-content">
             <div id="activity" class="<?= ($active_tab == 'activity') ? 'active' : '' ?>">
                 <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-lg p-6 md:p-8 transition-colors duration-300">
                     <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-6">Aktivitas Diskusi Terakhir</h2>
                     <ul class="space-y-6">
                         <?php if ($activities && $activities->num_rows > 0): ?>
                             <?php while($act = $activities->fetch_assoc()): ?>
                                 <li class="border-l-4 border-primary-500 pl-4 py-2 hover:bg-slate-50 dark:hover:bg-slate-700/30 rounded-r-lg transition-colors">
                                     <p class="text-slate-800 dark:text-slate-200">
                                         Anda berkomentar: <span class="font-semibold italic">"<?= htmlspecialchars(substr($act['komentar'], 0, 150)) . (strlen($act['komentar']) > 150 ? '...' : '') ?>"</span>
                                     </p>
                                     <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                                         <i class="bi bi-book mr-1"></i> Pada buku
                                         <a href="detail_buku.php?id=<?= $act['buku_id'] ?>" class="font-medium text-primary-600 dark:text-primary-400 hover:underline">
                                             <?= htmlspecialchars($act['judul_buku']) ?>
                                         </a>
                                         <span class="mx-2 text-slate-300 dark:text-slate-600">|</span>
                                         <i class="bi bi-clock mr-1"></i> <?= date('d M Y, H:i', strtotime($act['tanggal'])) ?>
                                     </p>
                                 </li>
                             <?php endwhile; ?>
                         <?php else: ?>
                             <p class="text-center text-slate-500 dark:text-slate-400 py-8">Belum ada aktivitas diskusi yang tercatat.</p>
                         <?php endif; ?>
                     </ul>
                     <?php if ($total_halaman > 1): ?>
                         <nav aria-label="Navigasi Aktivitas" class="mt-8 flex justify-center">
                             <ul class="flex items-center -space-x-px h-10 text-base">
                                 <li>
                                     <a href="?tab=activity&page=<?= max(1, $halaman_aktif - 1) ?>"
                                        class="flex items-center justify-center px-4 h-10 ms-0 leading-tight rounded-l-lg <?= ($halaman_aktif <= 1) ? 'text-slate-400 bg-slate-200 dark:bg-slate-700 dark:text-slate-500 cursor-not-allowed' : 'text-slate-600 bg-white border border-slate-300 hover:bg-slate-100 hover:text-slate-700 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-400 dark:hover:bg-slate-700 dark:hover:text-white' ?>">
                                         <span class="sr-only">Sebelumnya</span>
                                         <i class="bi bi-chevron-left"></i>
                                     </a>
                                 </li>
                                 <?php
                                   // Logic untuk menampilkan nomor halaman (misal: hanya 5 nomor sekitar halaman aktif)
                                   $start_page = max(1, $halaman_aktif - 2);
                                   $end_page = min($total_halaman, $halaman_aktif + 2);
                                   if ($halaman_aktif <= 3) $end_page = min($total_halaman, 5);
                                   if ($halaman_aktif >= $total_halaman - 2) $start_page = max(1, $total_halaman - 4);
 
                                   if ($start_page > 1) echo '<li><span class="flex items-center justify-center px-4 h-10 leading-tight text-slate-500 bg-white border border-slate-300 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-400">...</span></li>';
 
                                   for ($i = $start_page; $i <= $end_page; $i++):
                                 ?>
                                      <li>
                                         <a href="?tab=activity&page=<?= $i ?>" aria-current="<?= ($i == $halaman_aktif) ? 'page' : 'false' ?>"
                                            class="flex items-center justify-center px-4 h-10 leading-tight <?= ($i == $halaman_aktif) ? 'z-10 text-primary-600 border border-primary-300 bg-primary-50 hover:bg-primary-100 hover:text-primary-700 dark:border-slate-700 dark:bg-slate-700 dark:text-white font-bold' : 'text-slate-600 bg-white border border-slate-300 hover:bg-slate-100 hover:text-slate-700 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-400 dark:hover:bg-slate-700 dark:hover:text-white' ?>">
                                             <?= $i ?>
                                         </a>
                                     </li>
                                 <?php endfor;
 
                                   if ($end_page < $total_halaman) echo '<li><span class="flex items-center justify-center px-4 h-10 leading-tight text-slate-500 bg-white border border-slate-300 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-400">...</span></li>';
                                 ?>
                                 <li>
                                     <a href="?tab=activity&page=<?= min($total_halaman, $halaman_aktif + 1) ?>"
                                        class="flex items-center justify-center px-4 h-10 leading-tight rounded-r-lg <?= ($halaman_aktif >= $total_halaman) ? 'text-slate-400 bg-slate-200 dark:bg-slate-700 dark:text-slate-500 cursor-not-allowed' : 'text-slate-600 bg-white border border-slate-300 hover:bg-slate-100 hover:text-slate-700 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-400 dark:hover:bg-slate-700 dark:hover:text-white' ?>">
                                         <span class="sr-only">Selanjutnya</span>
                                         <i class="bi bi-chevron-right"></i>
                                     </a>
                                 </li>
                             </ul>
                         </nav>
                     <?php endif; ?>
                 </div>
             </div>
             <div id="settings" class="<?= ($active_tab == 'settings') ? 'active' : '' ?>">
                 <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-lg p-6 md:p-8 transition-colors duration-300">
                     <h2 class="text-xl font-bold text-slate-900 dark:text-white border-b border-slate-200 dark:border-slate-700 pb-3 mb-6">Informasi Profil</h2>
 
                     <?php if ($message && $active_tab == 'settings'): ?>
                         <div class="mb-6 p-4 text-sm rounded-lg <?= ($message_type == 'danger') ? 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-200' : 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200' ?>" role="alert">
                             <?= $message ?>
                         </div>
                     <?php endif; ?>
 
                     <form action="akun.php?tab=settings" method="POST" autocomplete="off" class="space-y-6">
                         <input type="hidden" name="action" value="update_profile">
 
                         <div class="grid grid-cols-1 md:grid-cols-3 gap-2 md:gap-4 items-center">
                           <label for="name" class="text-sm font-medium text-slate-700 dark:text-slate-300">Nama Lengkap</label>
                           <div class="md:col-span-2">
                               <input type="text" id="name" name="name" value="<?= $current_name ?>" required
                                      class="block w-full px-4 py-2 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition text-sm"/>
                           </div>
                         </div>
 
                         <div class="grid grid-cols-1 md:grid-cols-3 gap-2 md:gap-4 items-center">
                           <label for="email" class="text-sm font-medium text-slate-700 dark:text-slate-300">Alamat Email</label>
                           <div class="md:col-span-2">
                               <input type="email" id="email" name="email" value="<?= $current_email ?>" required
                                      class="block w-full px-4 py-2 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition text-sm"/>
                           </div>
                         </div>
 
                         <div class="grid grid-cols-1 md:grid-cols-3 gap-2 md:gap-4 items-start">
                             <label for="bio" class="text-sm font-medium text-slate-700 dark:text-slate-300 pt-2">Bio</label>
                             <div class="md:col-span-2">
                                <textarea id="bio" name="bio" rows="4"
                                          class="block w-full px-4 py-2 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition text-sm"
                                          placeholder="Ceritakan sedikit tentang diri Anda..."
                                ><?= $current_bio ?></textarea>
                                 <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Deskripsi singkat ini akan muncul di profil Anda.</p>
                             </div>
                         </div>
 
                         <div class="pt-4 flex justify-end border-t border-slate-200 dark:border-slate-700 mt-8">
                             <button type="submit" class="inline-flex items-center px-6 py-2 bg-primary-600 text-white font-semibold rounded-lg shadow-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:ring-offset-slate-800 transition-transform transform hover:scale-105 text-sm">
                                 <i class="bi bi-save-fill mr-2"></i> Simpan Perubahan
                             </button>
                         </div>
                     </form>
                 </div>
                 </div>
             </div>
         </main>
 </div>
 
 <script>
     document.addEventListener('DOMContentLoaded', function() {
         const tabs = document.querySelectorAll('.tab-button');
         const tabContents = document.querySelectorAll('.tab-content > div');
 
         // Fungsi untuk menampilkan tab dan update class tombol
         function showTab(tabId) {
             // Sembunyikan semua konten tab, lalu tampilkan yang sesuai ID
             tabContents.forEach(content => {
                 if (content.id === tabId) {
                     content.classList.add('active'); // Tampilkan konten yang dipilih
                 } else {
                     content.classList.remove('active'); // Sembunyikan yang lain
                 }
             });
 
             // Update style tombol tab
             tabs.forEach(tab => {
                 const isTargetTab = tab.getAttribute('data-tab') === tabId;
                 const activeClasses = ['border-primary-500', 'text-primary-600', 'dark:text-primary-400'];
                 const inactiveClasses = ['border-transparent', 'text-slate-500', 'hover:text-slate-700', 'hover:border-slate-300', 'dark:text-slate-400', 'dark:hover:text-slate-200', 'dark:hover:border-slate-600'];
 
                 tab.classList.remove(...(isTargetTab ? inactiveClasses : activeClasses));
                 tab.classList.add(...(isTargetTab ? activeClasses : inactiveClasses));
             });
 
             // Optional: Update URL hash tanpa reload (jika ingin bookmark/share state tab)
             // window.location.hash = '#' + tabId;
         }
 
         // Tambahkan event listener ke setiap tombol tab
         tabs.forEach(tab => {
             tab.addEventListener('click', function(event) {
                 event.preventDefault(); // Mencegah default action jika tombol adalah link <a>
                 const tabId = this.getAttribute('data-tab');
                 showTab(tabId);
 
                 // Update URL search parameter tanpa reload (agar state tab terjaga saat form disubmit)
                 const url = new URL(window.location);
                 url.searchParams.set('tab', tabId);
                 // Reset 'page' param when switching tabs away from activity
                 if (tabId !== 'activity') {
                     url.searchParams.delete('page');
                 }
                 window.history.pushState({}, '', url);
             });
         });
 
         // Tampilkan tab yang aktif berdasarkan URL search parameter atau state PHP saat load
         const urlParams = new URLSearchParams(window.location.search);
         const initialTabFromUrl = urlParams.get('tab');
         const initialTab = initialTabFromUrl || '<?= $active_tab ?>'; // Prioritaskan URL, fallback ke PHP
         showTab(initialTab);
     });
 </script>
 
 </body>
 </html>