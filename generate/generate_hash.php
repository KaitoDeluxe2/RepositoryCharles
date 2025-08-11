<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Password Hash Generator</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <link href="css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5 col-md-7">
    <div class="card">
        <div class="card-header">
            <h2>Password Hash Generator (Untuk Admin)</h2>
        </div>
        <div class="card-body">
            <p>Gunakan alat ini untuk membuat password admin yang aman sebelum dimasukkan ke database.</p>
            
            <?php
            $hashed_password = '';
            if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['password'])) {
                $password_to_hash = $_POST['password'];
                // Membuat hash password menggunakan algoritma BCRYPT yang aman
                $hashed_password = password_hash($password_to_hash, PASSWORD_BCRYPT);
            }
            ?>

            <form action="generate_hash.php" method="POST">
                <div class="mb-3">
                    <label for="password" class="form-label">Masukkan Password Admin:</label>
                    <input type="text" id="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary">Generate Hash</button>
            </form>

            <?php if ($hashed_password): ?>
            <div class="mt-4">
                <h4>Hasil Hash (Aman untuk Disimpan):</h4>
                <div class="alert alert-success">
                    <p><strong>Salin teks di bawah ini</strong> dan tempel ke kolom `password` di phpMyAdmin saat membuat user admin.</p>
                    <textarea class="form-control" rows="4" readonly onclick="this.select();"><?= htmlspecialchars($hashed_password); ?></textarea>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
