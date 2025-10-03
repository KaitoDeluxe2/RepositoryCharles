<?php
session_start();
include '../includes/db.php';

if (!isset($_GET['id']) || !isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$book_id = $_GET['id'];
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

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

// Ambil semua diskusi untuk buku ini DAN data avatar pengguna
$query = "SELECT d.id, d.user_id, d.username, d.komentar, d.tanggal, d.parent_id, u.avatar_seed 
          FROM diskusi d 
          JOIN users u ON d.user_id = u.id 
          WHERE d.buku_id = ? 
          ORDER BY d.tanggal ASC";
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

// --- [FUNGSI DIUBAH TOTAL] ---
// Fungsi rekursif untuk menampilkan komentar dan balasannya
function tampilkan_komentar($parent_id, $komentar, $book_id, $level = 0) {
    if (isset($komentar[$parent_id])) {
        foreach ($komentar[$parent_id] as $item) {
            $is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
            
            // Tentukan style inden. Jika level > 0 (ini adalah balasan), berikan margin.
            // Jika tidak (komentar utama), tidak ada margin.
            $indent_style = ($level > 0) 
                ? 'margin-left: 50px; padding-left: 15px; border-left: 2px solid #e9ecef;' 
                : '';

            // Wrapper untuk setiap item komentar
            echo '<div style="' . $indent_style . '">';
            
                echo '<div class="d-flex mb-3 comment-item pt-3">';
                    // Avatar
                    echo '<div class="flex-shrink-0 me-3">';
                    if (!empty($item['avatar_seed'])) {
                        $avatar_url = "https://api.dicebear.com/8.x/croodles/svg?seed=" . urlencode($item['avatar_seed']);
                        echo '<img src="' . $avatar_url . '" alt="Avatar" class="avatar" style="width: 48px; height: 48px;">';
                    } else {
                        // Fallback jika tidak ada avatar_seed
                        echo '<div class="avatar" style="background-color: hsl(' . (crc32($item['username']) % 360) . ', 60%, 50%); width: 48px; height: 48px;">
                                ' . strtoupper(substr($item['username'], 0, 1)) . '
                              </div>';
                    }
                    echo '</div>';
        
                    // Konten Komentar
                    echo '<div class="w-100">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="fw-bold mb-0">' . htmlspecialchars($item['username']) . '</h6>';
                    if ($is_admin) {
                        echo '<a href="delete_comment.php?id=' . $item['id'] . '&book_id=' . $book_id . '" 
                                   class="btn btn-sm btn-outline-danger admin-delete-btn"
                                   onclick="return confirm(\'Hapus komentar ini?\')"
                                   title="Hapus Komentar"><i class="bi bi-trash"></i></a>';
                    }
                    echo    '</div>
                            <p class="comment-meta mb-2">' . date('d F Y, H:i', strtotime($item['tanggal'])) . ' WIB</p>
                            <div class="comment-body">
                               <p class="mb-0">' . nl2br(htmlspecialchars($item['komentar'])) . '</p>
                            </div>
                            <button class="btn btn-sm btn-link text-decoration-none ps-0 mt-1 reply-btn" data-comment-id="' . $item['id'] . '"><i class="bi bi-reply-fill"></i> Balas</button>
                          </div>';
                echo '</div>'; // End .comment-item
            
            echo '</div>'; // End wrapper inden

            // Panggil rekursif untuk anak dari komentar ini.
            // Level + 1 akan memastikan anak-anaknya dianggap sebagai balasan.
            tampilkan_komentar($item['id'], $komentar, $book_id, $level + 1);
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
    <style>
        body { background-color: #f0f2f5; }
        .discussion-header .book-cover { width: 80px; height: 120px; object-fit: cover; border-radius: 0.5rem; box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
        .avatar { display: inline-flex; align-items: center; justify-content: center; background-color: #0d6efd; color: white; border-radius: 50%; font-weight: bold; font-size: 1.2rem; flex-shrink: 0; }
        .comment-form-box .avatar { width: 40px; height: 40px; font-size: 1rem;}
        .comment-item .avatar { width: 48px; height: 48px; }
        .comment-body { background-color: #e9ecef; border-radius: 1rem; padding: 0.75rem 1rem; word-wrap: break-word; }
        .comment-meta { font-size: 0.8rem; color: #6c757d; }
        /* Hapus style .comment-thread */
        .reply-btn { font-weight: bold; }
        #cancel-reply-btn { display: none; }
        .admin-delete-btn { opacity: 0; transition: opacity 0.2s ease-in-out; }
        .comment-item:hover .admin-delete-btn { opacity: 1; }
    </style>
</head>
<body>

<div class="container my-5">
    <div class="col-lg-9 mx-auto">
        
        <?php
        if (isset($_GET['success']) && $_GET['success'] == 'comment_deleted') {
            $deleted_user = isset($_GET['deleted_user']) ? htmlspecialchars($_GET['deleted_user']) : 'Pengguna';
            echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong>Berhasil!</strong> Komentar dari <strong>' . $deleted_user . '</strong> telah berhasil dihapus.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                  </div>';
        }
        ?>

        <div class="bg-white p-4 rounded-3 shadow-sm mb-4 discussion-header">
            <div class="row g-3 align-items-center">
                <div class="col-auto">
                    <img src="../<?= htmlspecialchars($book['cover_path']) ?>" class="book-cover" alt="Cover">
                </div>
                <div class="col">
                    <h1 class="h4 fw-bold mb-1"><?= htmlspecialchars($book['judul']) ?></h1>
                    <p class="fs-6 text-muted mb-0">Diskusi buku oleh: <?= htmlspecialchars($book['penulis']) ?></p>
                </div>
                <div class="col-auto">
                    <a href="detail_buku.php?id=<?= $book_id; ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
        
        <div id="comment-form-container">
            <div class="card shadow-sm mb-4 comment-form-box">
                <div class="card-body p-3">
                    <h5 class="card-title px-2" id="form-title">Tulis Komentar Anda</h5>
                    <div class="d-flex align-items-start p-2">
                        <div class="avatar me-3">
                           <?php
                           if (isset($_SESSION['avatar_seed']) && !empty($_SESSION['avatar_seed'])):
                               $avatar_url = "https://api.dicebear.com/8.x/croodles/svg?seed=" . urlencode($_SESSION['avatar_seed']);
                           ?>
                               <img src="<?= $avatar_url ?>" alt="Avatar" style="width: 40px; height: 40px; border-radius: 50%;">
                           <?php else: ?>
                               <?= strtoupper(substr($_SESSION['username'], 0, 1)) ?>
                           <?php endif; ?>
                        </div>
                        <form id="main-comment-form" action="tambah_komentar.php" method="POST" class="w-100">
                            <input type="hidden" name="buku_id" value="<?= $book_id ?>">
                            <input type="hidden" name="parent_id" id="parent_id_input" value="0">
                            <div class="mb-2">
                                <textarea name="komentar" class="form-control" rows="3" placeholder="Apa pendapatmu tentang buku ini?" required></textarea>
                            </div>
                            <div class="d-flex justify-content-end">
                                <button type="button" class="btn btn-sm btn-secondary me-2" id="cancel-reply-btn">Batal</button>
                                <button type="submit" class="btn btn-primary"><i class="bi bi-send-fill"></i> Kirim</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white p-4 rounded-3 shadow-sm">
            <h4 class="mb-4">Diskusi Terbaru</h4>
            <?php if (!empty($komentar)): ?>
                <?php tampilkan_komentar(0, $komentar, $book_id); ?>
            <?php else: ?>
                <p class="text-center text-muted">Belum ada diskusi untuk buku ini. Jadilah yang pertama!</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="../js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const formContainer = document.getElementById('comment-form-container');
    const mainForm = document.getElementById('main-comment-form');
    const parentIdInput = document.getElementById('parent_id_input');
    const formTitle = document.getElementById('form-title');
    const originalFormParent = formContainer.parentNode;
    const cancelReplyBtn = document.getElementById('cancel-reply-btn');

    document.querySelectorAll('.reply-btn').forEach(button => {
        button.addEventListener('click', function() {
            const commentId = this.getAttribute('data-comment-id');
            const commentItem = this.closest('.comment-item').parentNode; // Target the wrapper div
            
            parentIdInput.value = commentId;
            formTitle.textContent = 'Balas Komentar...';
            cancelReplyBtn.style.display = 'inline-block';
            
            // Pindahkan form komentar setelah wrapper komentar yang akan dibalas
            commentItem.parentNode.insertBefore(formContainer, commentItem.nextSibling);
        });
    });

    cancelReplyBtn.addEventListener('click', function() {
        parentIdInput.value = '0';
        formTitle.textContent = 'Tulis Komentar Anda';
        this.style.display = 'none';
        
        // Kembalikan form komentar ke posisi semula
        originalFormParent.insertBefore(formContainer, originalFormParent.children[1]);
    });
});
</script>
</body>
</html>