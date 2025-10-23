<?php
 session_start();
 include '../includes/db.php';
 
 // Keamanan dasar dan pengambilan data
 if (!isset($_GET['id']) || !isset($_SESSION['user_id'])) {
     header("Location: dashboard.php");
     exit;
 }
 
 $book_id = $_GET['id'];
 $user_id = $_SESSION['user_id'];
 
 // Ambil data buku
 $stmt_buku = $conn->prepare("SELECT judul, penulis, cover_path FROM buku WHERE id = ?");
 $stmt_buku->bind_param("i", $book_id);
 $stmt_buku->execute();
 $result_buku = $stmt_buku->get_result();
 if ($result_buku->num_rows === 0) {
     header("Location: dashboard.php"); exit;
 }
 $book = $result_buku->fetch_assoc();
 $stmt_buku->close();
 
 
 // Ambil ID komentar yang sudah di-like dan di-dislike oleh user saat ini
 $stmt_liked = $conn->prepare("SELECT comment_id FROM comment_likes WHERE user_id = ?");
 $stmt_liked->bind_param("i", $user_id);
 $stmt_liked->execute();
 $liked_comments_result = $stmt_liked->get_result();
 $liked_comments = array_column($liked_comments_result->fetch_all(MYSQLI_ASSOC), 'comment_id');
 $stmt_liked->close();
 
 $stmt_disliked = $conn->prepare("SELECT comment_id FROM comment_dislikes WHERE user_id = ?");
 $stmt_disliked->bind_param("i", $user_id);
 $stmt_disliked->execute();
 $disliked_comments_result = $stmt_disliked->get_result();
 $disliked_comments = array_column($disliked_comments_result->fetch_all(MYSQLI_ASSOC), 'comment_id');
 $stmt_disliked->close();
 
 
 // Query utama untuk komentar, ditambahkan join untuk mengambil username parent dan role user
 $query = "
     SELECT
         d.id, d.user_id, d.username, d.komentar, d.tanggal, d.parent_id,
         d.likes, d.dislikes, u.avatar_seed, u.role,
         parent.username AS parent_username,
         parent_user.id AS parent_user_id  /* Ambil ID user parent */
     FROM diskusi d
     LEFT JOIN users u ON d.user_id = u.id
     LEFT JOIN diskusi parent ON d.parent_id = parent.id
     LEFT JOIN users parent_user ON parent.user_id = parent_user.id /* Join ke users lagi untuk parent_user_id */
     WHERE d.buku_id = ?
     ORDER BY d.parent_id ASC, (d.likes - d.dislikes) DESC, d.tanggal ASC"; // Urutan diperbaiki
 
 $stmt_diskusi = $conn->prepare($query);
 $stmt_diskusi->bind_param("i", $book_id);
 $stmt_diskusi->execute();
 $diskusi_result = $stmt_diskusi->get_result();
 
 $komentar = [];
 while ($row = $diskusi_result->fetch_assoc()) {
     $komentar[$row['parent_id']][] = $row;
 }
 $stmt_diskusi->close();
 $conn->close();
 
 function tampilkan_komentar($parent_id, $komentar, $book_id, $liked_comments, $disliked_comments, $is_reply = false) {
     if (isset($komentar[$parent_id])) {
         
         foreach ($komentar[$parent_id] as $item) {
             $is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
             $is_owner = isset($_SESSION['user_id']) && $_SESSION['user_id'] == $item['user_id'];
 
             $is_liked = in_array($item['id'], $liked_comments);
             $is_disliked = in_array($item['id'], $disliked_comments);
             $like_btn_class = $is_liked ? 'btn-primary' : 'btn-outline-primary';
             $dislike_btn_class = $is_disliked ? 'btn-danger' : 'btn-outline-danger';
             $has_replies = isset($komentar[$item['id']]);
 
             // --- AVATAR ---
             $avatar_html = '<div class="flex-shrink-0 me-3">';
             if (!empty($item['avatar_seed'])) {
                 $avatar_url = "https://api.dicebear.com/8.x/croodles/svg?seed=" . urlencode($item['avatar_seed']) . "&backgroundColor=0d6efd";
                 $avatar_html .= '<img src="' . $avatar_url . '" alt="Avatar" class="avatar clickable-user" data-user-id="' . $item['user_id'] . '">';
             } else {
                 $fallback_seed = !empty($item['username']) ? $item['username'] : 'user_' . $item['user_id']; // Gunakan ID jika username kosong
                 $avatar_url = "https://api.dicebear.com/8.x/initials/svg?seed=" . urlencode($fallback_seed);
                 $avatar_html .= '<img src="' . $avatar_url . '" alt="Avatar" class="avatar clickable-user" data-user-id="' . $item['user_id'] . '">';
             }
             $avatar_html .= '</div>';
 
             // --- USERNAME DISPLAY ---
             $username_display = '<h6 class="fw-bold mb-0">';
             $username_display .= '<span class="clickable-user text-dark dark:text-white" data-user-id="' . $item['user_id'] . '">' . htmlspecialchars($item['username']) . '</span>';
             if ($item['role'] === 'admin') {
                 $username_display .= ' <span class="badge bg-primary ms-1">Admin</span>';
             }
             if (!empty($item['parent_username'])) {
                 $username_display .= ' <span class="text-muted fw-normal mx-1">membalas</span> ';
                 if (!empty($item['parent_user_id'])) {
                      $username_display .= '<span class="clickable-user text-info dark:text-info-light" data-user-id="' . $item['parent_user_id'] . '">' . htmlspecialchars($item['parent_username']) . '</span>';
                 } else {
                      $username_display .= '<span class="text-muted">' . htmlspecialchars($item['parent_username']) . '</span>'; // Style beda jika tidak bisa diklik
                 }
             }
             $username_display .= '</h6>';
 
             echo '<div class="comment-wrapper ' . ($is_reply ? '' : 'comment-wrapper-top-level') . '">';
                 echo '<div class="d-flex comment-item py-3">';
                     
                     echo $avatar_html; // Tampilkan avatar
         
                     echo '<div class="w-100">
                             <div class="d-flex justify-content-between align-items-center">'
                                 . $username_display . ' <div class="dropdown">
                                     <button class="btn btn-sm btn-link text-secondary dark:text-slate-400" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                         <i class="bi bi-three-dots-vertical"></i>
                                     </button>
                                     <ul class="dropdown-menu dropdown-menu-end dark:bg-slate-700 dark:border-slate-600">
                                     ';
                                     if ($is_owner) {
                                         echo '<li><a class="dropdown-item dark:text-slate-200 dark:hover:bg-slate-600 edit-btn" href="#" data-comment-id="' . $item['id'] . '"><i class="bi bi-pencil-fill me-2"></i>Edit</a></li>';
                                         echo '<li><a class="dropdown-item text-danger dark:text-red-400 dark:hover:bg-slate-600 delete-btn" href="#" data-comment-id="' . $item['id'] . '"><i class="bi bi-trash-fill me-2"></i>Hapus</a></li>';
                                     }
                                     if ($is_admin && !$is_owner) {
                                         echo '<li><a class="dropdown-item text-danger dark:text-red-400 dark:hover:bg-slate-600 admin-delete-btn" href="#" data-comment-id="' . $item['id'] . '" data-username="' . htmlspecialchars($item['username']) .'"><i class="bi bi-shield-fill-x me-2"></i>Hapus (Admin)</a></li>';
                                     }
                                     if (!$is_owner && !$is_admin) {
                                         echo '<li><span class="dropdown-item disabled text-muted dark:text-slate-500">Tidak ada aksi</span></li>';
                                     }
                                     echo '
                                     </ul>
                                 </div>
                             </div>
                             <p class="comment-meta mb-2 text-xs">' . date('d M Y, H:i', strtotime($item['tanggal'])) . '</p>
                             
                             <div class="comment-body-wrapper">
                                 <div class="comment-body bg-light dark:bg-slate-700 p-3 rounded-3 mb-2"><p class="mb-0 text-sm">' . nl2br(htmlspecialchars($item['komentar'])) . '</p></div>
                                 <div class="edit-form-wrapper" style="display: none;">
                                     <form action="user_edit_komentar.php" method="POST" class="mb-2">
                                         <input type="hidden" name="comment_id" value="' . $item['id'] . '">
                                         <input type="hidden" name="buku_id" value="' . $book_id . '">
                                         <textarea name="komentar" class="form-control form-control-sm mb-2 dark:bg-slate-600 dark:text-white dark:border-slate-500" rows="3">' . htmlspecialchars($item['komentar']) . '</textarea>
                                         <button type="submit" class="btn btn-primary btn-sm">Simpan</button>
                                         <button type="button" class="btn btn-secondary btn-sm cancel-edit-btn">Batal</button>
                                     </form>
                                 </div>
                             </div>
 
                             <div class="comment-actions mt-1">
                                 <button class="btn btn-sm btn-link text-decoration-none ps-0 fw-bold text-primary dark:text-primary-400 reply-btn" data-comment-id="' . $item['id'] . '"><i class="bi bi-reply-fill"></i> Balas</button>
                                 <button class="btn btn-sm ' . $like_btn_class . ' like-btn dark:border-slate-600 dark:text-slate-300" data-comment-id="' . $item['id'] . '">
                                     <i class="bi bi-hand-thumbs-up-fill"></i> 
                                     <span class="like-count">' . $item['likes'] . '</span>
                                 </button>
                                 <button class="btn btn-sm ' . $dislike_btn_class . ' dislike-btn dark:border-slate-600 dark:text-slate-300" data-comment-id="' . $item['id'] . '">
                                     <i class="bi bi-hand-thumbs-down-fill"></i>
                                     <span class="dislike-count">' . $item['dislikes'] . '</span>
                                 </button>
                             </div>';
 
                 if ($has_replies) {
                     $total_replies = count($komentar[$item['id']]);
                     echo '<div class="mt-2">
                             <button class="btn btn-link text-decoration-none fw-bold p-0 toggle-replies-btn text-xs" data-bs-toggle="collapse" data-bs-target="#replies-of-' . $item['id'] . '" aria-expanded="false">
                                 <i class="bi bi-caret-down-fill"></i> ' . $total_replies . ' balasan
                             </button>
                           </div>';
                 }
 
                 echo '</div></div>';
             
                 if ($has_replies) {
                     echo '<div class="collapse replies-container" id="replies-of-' . $item['id'] . '">';
                     tampilkan_komentar($item['id'], $komentar, $book_id, $liked_comments, $disliked_comments, true);
                     echo '</div>';
                 }
             echo '</div>';
         }
     }
 }
 ?>
 <!DOCTYPE html>
 <html lang="id">
 <head>
     <meta charset="UTF-8">
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <title>Diskusi: <?= htmlspecialchars($book['judul']) ?></title>
     <link href="../css/bootstrap.min.css" rel="stylesheet">
     <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
     
     <script>
         (function() {
             const theme = localStorage.getItem('theme');
             if (theme === 'dark') {
                 document.documentElement.classList.add('dark');
             } else {
                 document.documentElement.classList.remove('dark');
             }
         })();
     </script>
     
     <style>
         /* General Styling */
         body { background-color: #f8f9fa; }
         .container { max-width: 900px; }
 
         /* Dark Mode Base Styles */
         html.dark body { background-color: #18191a; color: #e4e6eb; }
         html.dark .bg-white { background-color: #242526 !important; } /* Override bg-white */
         html.dark .shadow-sm { box-shadow: 0 .125rem .25rem rgba(0,0,0,.3) !important; }
         html.dark .text-dark { color: #e4e6eb !important; }
         html.dark .text-muted { color: #b0b3b8 !important; }
         html.dark .border-top, html.dark .border-bottom, html.dark .border-start, html.dark .border-end, html.dark hr { border-color: #3a3b3c !important; }
         html.dark .btn-outline-secondary { color: #adb5bd; border-color: #495057; }
         html.dark .btn-outline-secondary:hover { color: #fff; background-color: #495057; }
         html.dark .form-control { background-color: #3a3b3c; color: #e4e6eb; border-color: #4a4a4d; }
         html.dark .form-control::placeholder { color: #8a8d91; }
         html.dark .badge.bg-primary { background-color: #3b82f6 !important; color: white; }
         html.dark .dropdown-menu { background-color: #343a40; border-color: #495057; }
         html.dark .dropdown-item { color: #dee2e6; }
         html.dark .dropdown-item:hover, html.dark .dropdown-item:focus { background-color: #495057; color: #fff; }
         html.dark .dropdown-item.text-danger { color: #e74c3c !important; }
         html.dark .dropdown-item.disabled { color: #6c757d !important; }
         html.dark .btn-close { filter: invert(1) grayscale(100%) brightness(200%); }
 
         /* Header Diskusi */
         .discussion-header .book-cover { width: 60px; height: 90px; object-fit: cover; border-radius: 0.3rem; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
         html.dark .discussion-header {
             background-color: #242526 !important;
             border-bottom: 1px solid #3a3b3c;
         }
         html.dark .discussion-header h1 { color: #e4e6eb; } /* Judul Buku */
 
         /* Form Komentar Utama */
         html.dark #comment-form-container > .card {
             background-color: #242526 !important;
             border-color: #3a3b3c !important;
         }
         html.dark #comment-form-container h5 { color: #e4e6eb; } /* "Tulis Komentar Anda" */
         .comment-form-box .avatar img, .comment-form-box .avatar div { width: 40px; height: 40px; }
 
         /* Container Daftar Komentar */
         html.dark .bg-white.p-4.rounded-3.shadow-sm:not(.discussion-header) {
             background-color: #242526 !important;
             border-color: #3a3b3c !important; /* Tambahkan border jika perlu */
         }
         html.dark .bg-white.p-4.rounded-3.shadow-sm:not(.discussion-header) h4 {
             color: #e4e6eb; /* "Diskusi Terbaru" */
             border-bottom-color: #3a3b3c !important;
         }
 
         /* Styling Komentar Individual */
         .avatar { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; flex-shrink: 0; background-color: #6c757d; display: flex; align-items: center; justify-content: center; }
         html.dark .avatar { background-color: #495057; } /* Background fallback avatar */
 
         .comment-wrapper-top-level { border-top: 1px solid #dee2e6; }
         .comment-wrapper-top-level:first-child { border-top: none; }
         html.dark .comment-wrapper-top-level { border-top-color: #3a3b3c; }
 
         .comment-meta { color: #6c757d; font-size: 0.75rem; }
         .comment-body { background-color: #f1f3f5; font-size: 0.9rem; }
         html.dark .comment-body { background-color: #3a3b3c !important; }
 
         .comment-actions { display: flex; align-items: center; gap: 0.5rem; }
         .comment-actions .btn { font-size: 0.8rem; padding: 0.2rem 0.6rem; }
         .like-count, .dislike-count { margin-left: 0.2rem; font-weight: 500; }
         html.dark .like-btn.btn-outline-primary, html.dark .dislike-btn.btn-outline-danger { border-color: #495057; color: #adb5bd; }
         html.dark .like-btn.btn-outline-primary:hover { background-color: #3b82f6; border-color: #3b82f6; color:white;}
         html.dark .dislike-btn.btn-outline-danger:hover { background-color: #dc3545; border-color: #dc3545; color:white;}
 
         /* Balasan (Replies) */
         .replies-container { margin-left: 40px; padding-left: 15px; border-left: 2px solid #e9ecef; }
         html.dark .replies-container { border-left-color: #3a3b3c; }
         .toggle-replies-btn { font-size: 0.8rem; padding: 0.1rem 0; color: #0d6efd !important; }
         html.dark .toggle-replies-btn { color: #60a5fa !important; }
         .toggle-replies-btn .bi { transition: transform 0.3s ease; display: inline-block; }
         .toggle-replies-btn[aria-expanded="true"] .bi { transform: rotate(-180deg); }
 
         /* Dropdown Aksi Komentar */
         .dropdown .btn-link { text-decoration: none; color: #6c757d; }
         html.dark .dropdown .btn-link { color: #adb5bd; }
         .dropdown-menu { font-size: 0.9rem; }
         .dropdown-menu .bi { margin-right: 0.5rem; }
 
         /* Clickable User & Preview Modal */
         .clickable-user { cursor: pointer; transition: opacity 0.2s ease-in-out; }
         .clickable-user:hover { opacity: 0.8; }
         #profilePreviewModal .modal-content { border-radius: 0.8rem; box-shadow: 0 10px 25px rgba(0,0,0,0.1); font-size: 0.9rem; }
         #profilePreviewModal .modal-body { padding-bottom: 1rem; }
         #profilePreviewModal #previewAvatar { width: 5rem; height: 5rem; border: 2px solid #dee2e6; }
         #profilePreviewModal #previewBio { max-height: 4.5em; line-height: 1.5em; overflow: hidden; text-overflow: ellipsis; font-size: 0.8rem; }
         #profilePreviewModal .modal-body > .d-flex { font-size: 0.8rem; }
 
         /* Penyesuaian Dark Mode Modal Bootstrap via CSS */
         html.dark #profilePreviewModal .modal-content { background-color: #242526; border-color: #3a3b3c; }
         html.dark #profilePreviewModal .modal-header .btn-close { filter: invert(1) grayscale(100%) brightness(200%); }
         html.dark #profilePreviewModal .modal-title { color: #e4e6eb; }
         html.dark #profilePreviewModal #previewBio { color: #b0b3b8; }
         html.dark #profilePreviewModal .modal-body > .d-flex .font-bold { color: #e4e6eb; }
         html.dark #profilePreviewModal .modal-body > .d-flex .text-slate-500 { color: #b0b3b8 !important; } /* Ganti ke !important jika perlu */
         html.dark #profilePreviewModal .modal-body > .d-flex { border-top-color: #3a3b3c !important; }
         html.dark #profilePreviewModal #previewAvatar { border-color: #4a4a4d; }
         html.dark .text-info-light { color: #6edff6 !important; } /* Warna text info untuk dark mode */
 
         /* Tombol Kirim Custom */
         #main-comment-form .btn-kirim {
             position: relative; outline: 0; border: 1px solid transparent; background-color: #488aec; color: #ffffff;
             font-size: 0.75rem; line-height: 1rem; font-weight: 700; text-transform: uppercase; padding: 0.75rem 1.5rem;
             border-radius: 0.5rem; cursor: pointer; display: flex; flex-direction: column; align-items: center;
             justify-content: center; vertical-align: middle; overflow: hidden;
             box-shadow: 0 4px 6px -1px #488aec31, 0 2px 4px -1px #488aec17;
         }
         #main-comment-form .btn-kirim span {
             height: 100%; width: 100%; display: flex; align-items: center; justify-content: center; gap: 0.75rem; transition: all .4s ease;
         }
         #main-comment-form .btn-kirim i { width: 1.1rem; height: 1.1rem; }
         #main-comment-form .btn-kirim span:nth-child(2), #main-comment-form .btn-kirim span:nth-child(3) {
             position: absolute; top: 100%; left: 0; color: #fff; background-color: #488aec; width: 100%; height: 100%; /* Pastikan mengisi tombol */
             display: flex; align-items: center; justify-content: center; /* Center teks di span hover/focus */
         }
         #main-comment-form .btn-kirim:hover { box-shadow: 0 10px 15px -3px #488aec4f, 0 4px 6px -2px #488aec17; }
         #main-comment-form .btn-kirim:hover span:nth-child(1) { top: -100%; }
         #main-comment-form .btn-kirim:hover span:nth-child(2) { top: 0; }
         #main-comment-form .btn-kirim:focus { box-shadow: none; }
         #main-comment-form .btn-kirim:focus span:nth-child(1) { top: -100%; }
         #main-comment-form .btn-kirim:focus span:nth-child(3) { top: 0; }
     </style>
 </head>
 <body>
     <div class="container my-5">
         <div class="col-lg-10 mx-auto">
             <div class="bg-white dark:bg-slate-800 p-4 rounded-3 shadow-sm mb-4 discussion-header d-flex justify-content-between align-items-center">
                 <div class="d-flex align-items-center">
                     <img src="../<?= htmlspecialchars($book['cover_path']) ?>" class="book-cover me-3" alt="Cover <?= htmlspecialchars($book['judul']) ?>">
                     <div>
                         <h1 class="h5 fw-bold mb-0 text-dark dark:text-white"><?= htmlspecialchars($book['judul']) ?></h1>
                         <p class="fs-sm text-muted mb-0">Forum Diskusi Buku</p>
                     </div>
                 </div>
                 <a href="detail_buku.php?id=<?= $book_id ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
             </div>
             
             <div id="comment-form-container">
                 <div class="card shadow-sm mb-4 comment-form-box dark:bg-slate-800 dark:border-slate-700">
                     <div class="card-body p-3">
                         <h5 class="card-title px-2 mb-3 h6 dark:text-white" id="form-title">Tulis Komentar Anda</h5>
                         <div class="d-flex align-items-start p-2">
                             <div class="avatar me-3 flex-shrink-0">
                                <?php if (isset($_SESSION['avatar_seed']) && !empty($_SESSION['avatar_seed'])): ?>
                                    <img src="<?= "https://api.dicebear.com/8.x/croodles/svg?seed=" . urlencode($_SESSION['avatar_seed']) . "&backgroundColor=0d6efd" ?>" alt="Avatar" class="w-100 h-100 rounded-circle">
                                <?php else:
                                    $fallback_seed_user = !empty($_SESSION['username']) ? $_SESSION['username'] : 'user_' . $_SESSION['user_id'];
                                    $avatar_url_user = "https://api.dicebear.com/8.x/initials/svg?seed=" . urlencode($fallback_seed_user);
                                ?>
                                    <img src="<?= $avatar_url_user ?>" alt="Avatar" class="w-100 h-100 rounded-circle">
                                <?php endif; ?>
                             </div>
                             <form id="main-comment-form" action="tambah_komentar.php" method="POST" class="w-100">
                                 <input type="hidden" name="buku_id" value="<?= $book_id ?>">
                                 <input type="hidden" name="parent_id" id="parent_id_input" value="0">
                                 <div class="mb-2">
                                     <textarea name="komentar" class="form-control form-control-sm dark:bg-slate-600 dark:text-white dark:border-slate-500" rows="3" placeholder="Bagikan pendapatmu tentang buku ini..." required></textarea>
                                 </div>
                                 <div class="d-flex justify-content-end align-items-center">
                                     <button type="button" class="btn btn-sm btn-secondary me-2" id="cancel-reply-btn" style="display: none;">Batal Balas</button>
                                     <button type="submit" class="btn btn-kirim">
                                         <span><i class="bi bi-send-fill"></i> Kirim</span>
                                         <span>Yakin?</span>
                                         <span>Mengirim...</span>
                                     </button>
                                 </div>
                             </form>
                         </div>
                     </div>
                 </div>
             </div>
 
             <div class="bg-white dark:bg-slate-800 p-4 rounded-3 shadow-sm">
                 <h4 class="mb-4 h5 border-bottom dark:border-slate-700 pb-2 dark:text-white">Diskusi Terbaru</h4>
                 <div id="comments-list">
                     <?php if (isset($komentar[0]) && count($komentar[0]) > 0): ?>
                         <?php tampilkan_komentar(0, $komentar, $book_id, $liked_comments, $disliked_comments); ?>
                     <?php else: ?>
                         <p class="text-center text-muted dark:text-slate-400 py-5">Belum ada diskusi untuk buku ini. Jadilah yang pertama!</p>
                     <?php endif; ?>
                 </div>
             </div>
         </div>
     </div>
 
     <div class="modal fade" id="profilePreviewModal" tabindex="-1" aria-labelledby="profilePreviewModalLabel" aria-hidden="true">
       <div class="modal-dialog modal-dialog-centered modal-sm">
         <div class="modal-content dark:bg-slate-800 dark:border-slate-700">
           <div class="modal-header border-0 pb-0">
             <button type="button" class="btn-close dark:filter dark:invert dark:grayscale dark:brightness-200" data-bs-dismiss="modal" aria-label="Close"></button>
           </div>
           <div class="modal-body text-center pt-0 pb-3">
             <img id="previewAvatar" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" alt="Avatar" class="w-20 h-20 rounded-full mx-auto mb-3 border-2 dark:border-slate-600 shadow-sm bg-secondary">
             <h5 id="previewUsername" class="modal-title text-lg font-bold mb-1 dark:text-white">Memuat...</h5>
             <p id="previewBio" class="text-xs text-slate-500 dark:text-slate-400 mb-3 px-2"></p>
             <div class="d-flex justify-content-around text-xs border-top dark:border-slate-700 pt-2 mt-3">
               <div class="text-center px-2">
                 <div id="previewDiskusi" class="font-bold text-lg dark:text-white">-</div>
                 <div class="text-slate-500 dark:text-slate-400">Diskusi</div>
               </div>
               <div class="text-center px-2">
                 <div id="previewSuka" class="font-bold text-lg dark:text-white">-</div>
                 <div class="text-slate-500 dark:text-slate-400">Suka Diterima</div>
               </div>
             </div>
           </div>
           </div>
       </div>
     </div>
     <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
 
     <script>
     document.addEventListener('DOMContentLoaded', function() {
 
         // --- Script Pembaca Tema di Head (dijalankan lagi di sini jika perlu re-apply) ---
         // Anda bisa memindahkan ini ke fungsi terpisah jika lebih rapi
         function applyTheme() {
             const theme = localStorage.getItem('theme');
             if (theme === 'dark') {
                 document.documentElement.classList.add('dark');
             } else {
                 document.documentElement.classList.remove('dark');
             }
         }
         applyTheme(); // Terapkan tema saat DOM siap
 
         // --- LOGIKA FORM BALASAN ---
         const formContainer = document.getElementById('comment-form-container');
         const originalFormParent = document.querySelector('.col-lg-10.mx-auto');
         const parentIdInput = document.getElementById('parent_id_input');
         const formTitle = document.getElementById('form-title');
         const cancelReplyBtn = document.getElementById('cancel-reply-btn');
         const mainCommentForm = document.getElementById('main-comment-form');
         const mainTextarea = mainCommentForm.querySelector('textarea');
         const discussionListContainer = document.getElementById('comments-list');
 
         discussionListContainer.addEventListener('click', function(event) {
             const replyButton = event.target.closest('.reply-btn');
             if (replyButton) {
                 event.preventDefault();
                 const commentId = replyButton.dataset.commentId;
                 const commentWrapper = replyButton.closest('.comment-wrapper');
                 const replyingToUsernameElement = commentWrapper.querySelector('.clickable-user[data-user-id]'); // Target span username
                 const replyingToUsername = replyingToUsernameElement ? replyingToUsernameElement.textContent : 'komentar'; // Fallback
 
                 parentIdInput.value = commentId;
                 formTitle.textContent = 'Balas ke ' + replyingToUsername;
                 cancelReplyBtn.style.display = 'inline-block';
                 mainTextarea.placeholder = 'Tulis balasan Anda...';
                 mainTextarea.focus();
                 commentWrapper.appendChild(formContainer);
             }
         });
 
         cancelReplyBtn.addEventListener('click', function() {
             parentIdInput.value = '0';
             formTitle.textContent = 'Tulis Komentar Anda';
             this.style.display = 'none';
             mainTextarea.placeholder = 'Bagikan pendapatmu tentang buku ini...';
             originalFormParent.insertBefore(formContainer, discussionListContainer.parentElement);
         });
 
         // --- LOGIKA LIKE & DISLIKE (AJAX) ---
         discussionListContainer.addEventListener('click', function(event){
            const targetButton = event.target.closest('.like-btn, .dislike-btn');
            if(!targetButton) return;
 
            event.preventDefault();
            const commentId = targetButton.dataset.commentId;
            const isLikeButton = targetButton.classList.contains('like-btn');
            const handlerUrl = isLikeButton ? 'like_handler.php' : 'dislike_handler.php';
 
            const actionsContainer = targetButton.closest('.comment-actions');
            const likeBtn = actionsContainer.querySelector('.like-btn');
            const dislikeBtn = actionsContainer.querySelector('.dislike-btn');
            const likeCountSpan = likeBtn.querySelector('.like-count');
            const dislikeCountSpan = dislikeBtn.querySelector('.dislike-count');
 
             fetch(handlerUrl, {
                 method: 'POST',
                 headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                 body: 'comment_id=' + commentId
             })
             .then(response => response.json())
             .then(data => {
                 if (data.success) {
                     likeCountSpan.textContent = data.new_like_count;
                     dislikeCountSpan.textContent = data.new_dislike_count;
 
                     likeBtn.classList.toggle('btn-primary', data.liked);
                     likeBtn.classList.toggle('btn-outline-primary', !data.liked);
 
                     dislikeBtn.classList.toggle('btn-danger', data.disliked);
                     dislikeBtn.classList.toggle('btn-outline-danger', !data.disliked);
 
                 } else {
                     alert(data.message || 'Gagal memproses aksi.');
                 }
             })
             .catch(error => {
                 console.error('Error:', error);
                 alert('Terjadi kesalahan koneksi.');
             });
         });
 
         // --- LOGIKA EDIT & HAPUS (User & Admin via AJAX) ---
         discussionListContainer.addEventListener('click', function(event) {
             const target = event.target;
 
             if (target.classList.contains('edit-btn') || target.closest('.edit-btn')) {
                 event.preventDefault();
                 const button = target.closest('.edit-btn');
                 const commentItem = button.closest('.comment-item');
                 const wrapper = commentItem.querySelector('.comment-body-wrapper');
                 wrapper.querySelector('.comment-body').style.display = 'none';
                 wrapper.querySelector('.edit-form-wrapper').style.display = 'block';
                 wrapper.querySelector('.edit-form-wrapper textarea').focus();
             }
 
             if (target.classList.contains('cancel-edit-btn') || target.closest('.cancel-edit-btn')) {
                 event.preventDefault();
                 const button = target.closest('.cancel-edit-btn');
                 const wrapper = button.closest('.comment-body-wrapper');
                 wrapper.querySelector('.edit-form-wrapper').style.display = 'none';
                 wrapper.querySelector('.comment-body').style.display = 'block';
             }
 
             if (target.classList.contains('delete-btn') || target.closest('.delete-btn')) {
                 event.preventDefault();
                 const button = target.closest('.delete-btn');
                 const commentId = button.dataset.commentId;
                 if (confirm('Apakah Anda yakin ingin menghapus komentar ini?')) {
                     fetch('user_hapus_komentar.php', {
                         method: 'POST',
                         headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                         body: 'comment_id=' + commentId
                     })
                     .then(response => response.json())
                     .then(data => {
                         if (data.success) {
                             button.closest('.comment-wrapper').remove();
                         } else {
                             alert(data.message || 'Gagal menghapus komentar.');
                         }
                     })
                     .catch(error => { console.error('Error deleting comment:', error); alert('Terjadi kesalahan koneksi.'); });
                 }
             }
 
             if (target.classList.contains('admin-delete-btn') || target.closest('.admin-delete-btn')) {
                 event.preventDefault();
                 const button = target.closest('.admin-delete-btn');
                 const commentId = button.dataset.commentId;
                 const username = button.dataset.username;
                 if (confirm(`ADMIN: Hapus komentar dari ${username} ini?`)) {
                     fetch('user_hapus_komentar.php', { // Pakai endpoint user_hapus yg sudah ada logic cek admin
                         method: 'POST',
                         headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                         body: 'comment_id=' + commentId
                     })
                     .then(response => response.json())
                     .then(data => {
                         if (data.success) {
                             button.closest('.comment-wrapper').remove();
                         } else {
                             alert('ADMIN: ' + (data.message || 'Gagal menghapus komentar.'));
                         }
                     })
                     .catch(error => { console.error('Admin delete error:', error); alert('ADMIN: Terjadi kesalahan koneksi.'); });
                 }
             }
         });
 
         // --- Logika untuk Profile Preview Modal ---
         const profileModalElement = document.getElementById('profilePreviewModal');
         let profileModal = null; // Inisialisasi nanti
         if (profileModalElement) {
             profileModal = new bootstrap.Modal(profileModalElement);
         }
 
         const previewAvatar = document.getElementById('previewAvatar');
         const previewUsername = document.getElementById('previewUsername');
         const previewBio = document.getElementById('previewBio');
         const previewDiskusi = document.getElementById('previewDiskusi');
         const previewSuka = document.getElementById('previewSuka');
         // const previewFullProfileLink = document.getElementById('previewFullProfileLink');
 
         discussionListContainer.addEventListener('click', function(event) {
             const target = event.target.closest('.clickable-user');
 
             if (target && profileModal) { // Pastikan modal sudah diinisialisasi
                 event.preventDefault();
                 const userId = target.dataset.userId;
                 if (!userId) return;
 
                 // Reset modal & Tampilkan loading
                 if(previewAvatar) previewAvatar.src = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';
                 if(previewUsername) previewUsername.textContent = 'Memuat...';
                 if(previewBio) previewBio.textContent = '';
                 if(previewDiskusi) previewDiskusi.textContent = '-';
                 if(previewSuka) previewSuka.textContent = '-';
                 // if (previewFullProfileLink) previewFullProfileLink.href = '#';
                 profileModal.show();
 
                 fetch(`get_user_preview.php?user_id=${userId}`)
                     .then(response => {
                         if (!response.ok) { throw new Error('Network response error'); }
                         return response.json();
                     })
                     .then(data => {
                         if (data.success && data.user) {
                             if(previewAvatar) previewAvatar.src = data.user.avatarUrl;
                             if(previewUsername) previewUsername.textContent = data.user.username;
                             if(previewBio) previewBio.textContent = data.user.bio;
                             if(previewDiskusi) previewDiskusi.textContent = data.user.total_diskusi;
                             if(previewSuka) previewSuka.textContent = data.user.total_suka;
                             // if (previewFullProfileLink) previewFullProfileLink.href = `akun.php?user_id=${userId}`;
                         } else {
                             if(previewUsername) previewUsername.textContent = 'Error';
                             if(previewBio) previewBio.textContent = data.message || 'Gagal memuat profil.';
                         }
                     })
                     .catch(error => {
                         console.error('Error fetching user preview:', error);
                         if(previewUsername) previewUsername.textContent = 'Error';
                         if(previewBio) previewBio.textContent = 'Tidak dapat terhubung.';
                     });
             }
         });
 
     }); // Akhir DOMContentLoaded
     </script>
 </body>
 </html>