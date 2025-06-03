<?php
session_start();
require_once 'functions.php';

// Mengecek apakah ada email yang tersimpan di $_SESSION, yang mengindikasikan bahwa user sudah login.
function isUserLoggedIn(): bool {
    return isset($_SESSION['email']);
}

// Jika user belum login, akan langsung diarahkan ke halaman login menggunakan header() lalu exit() untuk menghentikan eksekusi lebih lanjut.
function redirectIfNotLoggedIn(string $redirectUrl): void {
    if (!isUserLoggedIn()) {
        header("Location: $redirectUrl");
        exit();
    }
}

// untuk memastikan hanya user yang sudah login bisa mengakses halaman ini.
redirectIfNotLoggedIn('login.html');

// Ambil data user dari database menggunakan UserManager
require_once 'config.php';
$userManager = new UserManager($pdo);
$user = $userManager->getUserByEmail($_SESSION['email'] ?? '');

// Proses update jika form disubmit (blok pemrosesan request POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $updated = false;
    try {
        // Ambil data lama dari session
        $oldFullName = $_SESSION['full_name'] ?? '';
        $oldWarmindoName = $_SESSION['warmindo_name'] ?? '';

        // Ambil data baru dari POST
        $newFullName = $_POST['full_name'] ?? '';
        $newWarmindoName = $_POST['warmindo_name'] ?? '';

        // Cek apakah ada perubahan, Jika nama lengkap baru berbeda dari yang lama:
        if ($newFullName !== $oldFullName) {
            $userManager->setFullName($newFullName); // Set nilai baru ke objek userManager (dengan setter).
            $userManager->updateFullName($user['id'], $userManager->getFullName()); // Update ke database lewat method updateFullName.
            $_SESSION['full_name'] = $userManager->getFullName(); // Update session dengan nama lengkap terbaru.
            $updated = true;
        }
        if ($newWarmindoName !== $oldWarmindoName) {
            $userManager->setWarmindoName($newWarmindoName);
            $userManager->updateWarmindoName($user['id'], $userManager->getWarmindoName());
            $_SESSION['warmindo_name'] = $userManager->getWarmindoName();
            $updated = true;
        }

        if ($updated) {
            $_SESSION['success_message'] = 'Perubahan berhasil disimpan!';
        } else {
            $_SESSION['error_message'] = 'Tidak ada perubahan yang disimpan.';
        }
    } catch (Exception $e) {
        $_SESSION['error_message'] = 'Gagal menyimpan perubahan: ' . $e->getMessage();
    }
    header('Location: pengaturan.php');
    exit;
}

// Pengambilan data user dan simpan ke session
if ($user) {
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['warmindo_name'] = $user['warmindo_name'];
    $_SESSION['email'] = $_SESSION['email'];
} else {
    // Jika user tidak ditemukan, logout paksa
    session_destroy();
    header("Location: login.html");
    exit();
}

// Mengambil data dari $_SESSION dengan keamanan tambahan melalui htmlspecialchars agar aman ditampilkan di HTML (mencegah XSS).
function getSessionData(string $key): string {
    return htmlspecialchars($_SESSION[$key] ?? '');
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informasi Pengguna</title>
    <link rel="stylesheet" href="pengaturan.css">
</head>
<body>
    <div class="container">
        <button class="btn back" type="button" onclick="goBack()">&#8617; kembali</button>
        <h1 class="informasi-pengguna">Informasi Pengguna</h1>
        
        <form method="post" class="profile-info" id="profileForm">
            <p><strong>Nama Lengkap:</strong> 
                <input type="text" name="full_name" id="fullName" value="<?= getSessionData('full_name'); ?>" readonly autocomplete="off">
            </p>
            <p><strong>Nama Warmindo:</strong> 
                <input type="text" name="warmindo_name" id="warmindoName" value="<?= getSessionData('warmindo_name'); ?>" readonly autocomplete="off">
            </p>
            <p><strong>Email:</strong> 
                <input type="email" id="email" value="<?= getSessionData('email'); ?>" readonly>
            </p>
            <button type="button" class="btn edit" id="editBtn">Edit</button>
            <button type="submit" class="btn save" id="saveBtn" disabled>Simpan Perubahan</button>
        </form>

        <!-- Notifikasi -->
        <?php if (isset($_SESSION['success_message'])) : ?>
            <div id="success-alert" class="alert alert-success" role="alert">
                <strong>Berhasil!</strong> <?= $_SESSION['success_message']; ?>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])) : ?>
            <div id="error-alert" class="alert alert-danger" role="alert">
                <strong>Gagal!</strong> <?= $_SESSION['error_message']; ?>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <form action="logout.php" method="post">
            <button type="submit" class="btn exit">Keluar</button>
        </form>
    </div>
    <script src="pengaturan.js"></script>
</body>
</html>