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


// Query utama untuk komentar, ditambahkan join untuk mengambil username parent
$query = "
    SELECT 
        d.id, d.user_id, d.username, d.komentar, d.tanggal, d.parent_id, 
        d.likes, d.dislikes, u.avatar_seed, 
        parent.username AS parent_username 
    FROM diskusi d 
    LEFT JOIN users u ON d.user_id = u.id 
    LEFT JOIN diskusi parent ON d.parent_id = parent.id
    WHERE d.buku_id = ? 
    ORDER BY d.parent_id ASC, (d.likes - d.dislikes) DESC, d.tanggal ASC";

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
            $is_liked = in_array($item['id'], $liked_comments);
            $is_disliked = in_array($item['id'], $disliked_comments);
            $like_btn_class = $is_liked ? 'btn-primary' : 'btn-outline-primary';
            $dislike_btn_class = $is_disliked ? 'btn-danger' : 'btn-outline-danger';
            $has_replies = isset($komentar[$item['id']]);

            $username_display = '<h6 class="fw-bold mb-0">';
            $username_display .= htmlspecialchars($item['username']);
            if (!empty($item['parent_username'])) {
                $username_display .= ' <span class="text-muted fw-normal">membalas</span> ' . htmlspecialchars($item['parent_username']);
            }
            $username_display .= '</h6>';


            echo '<div class="comment-wrapper ' . ($is_reply ? '' : 'comment-wrapper-top-level') . '">';
                
                echo '<div class="d-flex comment-item py-3">';
                    echo '<div class="flex-shrink-0 me-3">';
                    if (!empty($item['avatar_seed'])) {
                        $avatar_url = "https://api.dicebear.com/8.x/croodles/svg?seed=" . urlencode($item['avatar_seed']) . "&backgroundColor=0d6efd";
                        echo '<img src="' . $avatar_url . '" alt="Avatar" class="avatar">';
                    } else {
                        echo '<div class="avatar" style="background-color: #6c757d;"><i class="bi bi-person-fill text-white"></i></div>';
                    }
                    echo '</div>';
        
                    echo '<div class="w-100">
                            <div class="d-flex justify-content-between align-items-center">'
                                . $username_display;
                    if ($is_admin) {
                        echo '<a href="delete_comment.php?id=' . $item['id'] . '&book_id=' . $book_id . '" 
                                   class="btn btn-sm btn-outline-danger admin-delete-btn"
                                   onclick="return confirm(\'Hapus komentar ini?\')"
                                   title="Hapus Komentar"><i class="bi bi-trash"></i></a>';
                    }
                    echo    '</div>
                            <p class="comment-meta mb-2">' . date('d F Y, H:i', strtotime($item['tanggal'])) . ' WIB</p>
                            <div class="comment-body bg-light p-3 rounded-3"><p class="mb-0">' . nl2br(htmlspecialchars($item['komentar'])) . '</p></div>
                            <div class="comment-actions mt-2">
                                <button class="btn btn-sm btn-link text-decoration-none ps-0 fw-bold reply-btn" data-comment-id="' . $item['id'] . '"><i class="bi bi-reply-fill"></i> Balas</button>
                                <button class="btn btn-sm ' . $like_btn_class . ' like-btn" data-comment-id="' . $item['id'] . '">
                                    <i class="bi bi-hand-thumbs-up-fill"></i> 
                                    <span class="like-count">' . $item['likes'] . '</span>
                                </button>
                                <button class="btn btn-sm ' . $dislike_btn_class . ' dislike-btn" data-comment-id="' . $item['id'] . '">
                                    <i class="bi bi-hand-thumbs-down-fill"></i>
                                    <span class="dislike-count">' . $item['dislikes'] . '</span>
                                </button>
                            </div>';

                if ($has_replies) {
                    $total_replies = count($komentar[$item['id']]);
                    echo '<div class="mt-2">
                            <button class="btn btn-link text-decoration-none fw-bold p-0 toggle-replies-btn" data-bs-toggle="collapse" data-bs-target="#replies-of-' . $item['id'] . '">
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
                document.documentElement.classList.add('dark-mode');
            }
        })();
    </script>
    
    <style>
        body { background-color: #f8f9fa; }
        .discussion-header .book-cover { width: 80px; height: 120px; object-fit: cover; border-radius: 0.5rem; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        .avatar { width: 48px; height: 48px; display: inline-flex; align-items: center; justify-content: center; background-color: #0d6efd; color: white; border-radius: 50%; font-weight: bold; font-size: 1.5rem; flex-shrink: 0; }
        .comment-form-box .avatar { width: 40px; height: 40px; font-size: 1rem; }
        #cancel-reply-btn { display: none; }
        .admin-delete-btn { opacity: 0; transition: opacity 0.2s ease-in-out; }
        .comment-item:hover .admin-delete-btn { opacity: 1; }
        .comment-actions { display: flex; align-items: center; gap: 0.5rem; }
        .like-btn, .dislike-btn { border-radius: 20px; padding: 0.2rem 0.8rem; font-size: 0.8rem; }
        .like-count, .dislike-count { margin-left: 0.3rem; font-weight: bold; }
        .comment-wrapper-top-level { border-top: 1px solid #dee2e6; }
        .comment-wrapper-top-level:first-child { border-top: none; }
        .replies-container { margin-left: 50px; padding-left: 15px; border-left: 2px solid #e9ecef; }
        .toggle-replies-btn { font-size: 0.9rem; padding: 0.2rem 0; color: #0d6efd !important; }
        .toggle-replies-btn .bi { transition: transform 0.3s ease; }
        .toggle-replies-btn[aria-expanded="true"] .bi { transform: rotate(-180deg); }
        .comment-meta { color: #6c757d; }

        /* --- CSS BARU UNTUK DARK MODE --- */
        html.dark-mode body { background-color: #18191a; color: #e4e6eb; }
        html.dark-mode .bg-white, html.dark-mode .card, html.dark-mode .bg-light { background-color: #242526 !important; color: #e4e6eb; border-color: #3a3b3c !important; }
        html.dark-mode h1, html.dark-mode h2, html.dark-mode h3, html.dark-mode h4, html.dark-mode h5, html.dark-mode h6 { color: #e4e6eb; }
        html.dark-mode .text-muted, html.dark-mode .comment-meta { color: #b0b3b8 !important; }
        html.dark-mode .form-control { background-color: #3a3b3c; color: #e4e6eb; border-color: #4a4a4d; }
        html.dark-mode .form-control::placeholder { color: #b0b3b8; }
        html.dark-mode .comment-body { background-color: #3a3b3c !important; }
        html.dark-mode .btn-outline-secondary { color: #e4e6eb; border-color: #6c757d; }
        html.dark-mode .btn-outline-secondary:hover { background-color: #6c757d; color: white; }
        html.dark-mode .comment-wrapper-top-level { border-top-color: #3a3b3c; }
        html.dark-mode .replies-container { border-left-color: #3a3b3c; }
    </style>
</head>
<body>
    <div class="container my-5">
        <div class="col-lg-9 mx-auto">
            <div class="bg-white p-4 rounded-3 shadow-sm mb-4 discussion-header">
                <div class="row g-3 align-items-center">
                    <div class="col-auto"><img src="../<?= htmlspecialchars($book['cover_path']) ?>" class="book-cover" alt="Cover"></div>
                    <div class="col"><h1 class="h4 fw-bold mb-1"><?= htmlspecialchars($book['judul']) ?></h1><p class="fs-6 text-muted mb-0">Diskusi buku</p></div>
                    <div class="col-auto"><a href="detail_buku.php?id=<?= $book_id ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Kembali</a></div>
                </div>
            </div>
            
            <div id="comment-form-container">
                <div class="card shadow-sm mb-4 comment-form-box">
                    <div class="card-body p-3">
                        <h5 class="card-title px-2" id="form-title">Tulis Komentar Anda</h5>
                        <div class="d-flex align-items-start p-2">
                            <div class="avatar me-3">
                               <?php if (isset($_SESSION['avatar_seed']) && !empty($_SESSION['avatar_seed'])): ?>
                                   <img src="<?= "https://api.dicebear.com/8.x/croodles/svg?seed=" . urlencode($_SESSION['avatar_seed']) . "&backgroundColor=0d6efd" ?>" alt="Avatar" style="width: 40px; height: 40px; border-radius: 50%;">
                               <?php else: echo strtoupper(substr($_SESSION['username'], 0, 1)); endif; ?>
                            </div>
                            <form id="main-comment-form" action="tambah_komentar.php" method="POST" class="w-100">
                                <input type="hidden" name="buku_id" value="<?= $book_id ?>"><input type="hidden" name="parent_id" id="parent_id_input" value="0">
                                <div class="mb-2"><textarea name="komentar" class="form-control" rows="3" placeholder="Apa pendapatmu tentang buku ini?" required></textarea></div>
                                <div class="d-flex justify-content-end"><button type="button" class="btn btn-sm btn-secondary me-2" id="cancel-reply-btn">Batal</button><button type="submit" class="btn btn-primary"><i class="bi bi-send-fill"></i> Kirim</button></div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white p-4 rounded-3 shadow-sm">
                <h4 class="mb-4">Diskusi Terbaru</h4>
                <?php if (!empty($komentar)): ?>
                    <?php tampilkan_komentar(0, $komentar, $book_id, $liked_comments, $disliked_comments); ?>
                <?php else: ?>
                    <p class="text-center text-muted">Belum ada diskusi untuk buku ini. Jadilah yang pertama!</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const formContainer = document.getElementById('comment-form-container');
        const originalFormParent = document.querySelector('.col-lg-9.mx-auto');
        const parentIdInput = document.getElementById('parent_id_input');
        const formTitle = document.getElementById('form-title');
        const cancelReplyBtn = document.getElementById('cancel-reply-btn');
        const discussionContainer = document.querySelectorAll('.bg-white.p-4.rounded-3.shadow-sm')[1];

        document.querySelectorAll('.reply-btn').forEach(button => {
            button.addEventListener('click', function() {
                const commentId = this.dataset.commentId;
                const commentWrapper = this.closest('.comment-wrapper');
                
                parentIdInput.value = commentId;
                formTitle.textContent = 'Balas Komentar...';
                cancelReplyBtn.style.display = 'inline-block';
                
                commentWrapper.appendChild(formContainer);
            });
        });

        cancelReplyBtn.addEventListener('click', function() {
            parentIdInput.value = '0';
            formTitle.textContent = 'Tulis Komentar Anda';
            this.style.display = 'none';
            originalFormParent.insertBefore(formContainer, discussionContainer);
        });

        document.querySelectorAll('.like-btn').forEach(button => {
            button.addEventListener('click', function() {
                const commentId = this.dataset.commentId;
                const likeCountSpan = this.querySelector('.like-count');

                fetch('like_handler.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'comment_id=' + commentId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        likeCountSpan.textContent = data.new_like_count;
                        if (data.liked) {
                            this.classList.remove('btn-outline-primary');
                            this.classList.add('btn-primary');
                        } else {
                            this.classList.remove('btn-primary');
                            this.classList.add('btn-outline-primary');
                        }
                    } else {
                        alert(data.message || 'Gagal memberikan like.');
                    }
                })
                .catch(error => console.error('Error:', error));
            });
        });
        
        document.querySelectorAll('.dislike-btn').forEach(button => {
            button.addEventListener('click', function() {
                const commentId = this.dataset.commentId;
                const dislikeCountSpan = this.querySelector('.dislike-count');
                fetch('dislike_handler.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'comment_id=' + commentId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        dislikeCountSpan.textContent = data.new_dislike_count;
                        if (data.disliked) {
                            this.classList.remove('btn-outline-danger');
                            this.classList.add('btn-danger');
                        } else {
                            this.classList.remove('btn-danger');
                            this.classList.add('btn-outline-danger');
                        }
                    } else {
                        alert(data.message || 'Gagal memberikan dislike.');
                    }
                })
                .catch(error => console.error('Error:', error));
            });
        });
    });
    </script>
</body>
</html>