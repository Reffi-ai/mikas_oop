<?php
session_start();
require_once 'functions.php'; // Tambahkan ini

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
require_once 'config.php'; // Pastikan koneksi $conn tersedia
$userManager = new UserManager($pdo);
$user = $userManager->getUserByEmail($_SESSION['email'] ?? '');

if ($user) {
    // Simpan ke session jika belum ada (opsional, jika ingin update session)
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['warmindo_name'] = $user['warmindo_name'];
    $_SESSION['email'] = $_SESSION['email']; // Sudah ada
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
        <button class="btn back" onclick="goBack()">&#8617; kembali</button>
        <h1 class="informasi-pengguna">Informasi Pengguna</h1>
        
        <div class="profile-info">
            <p><strong>Nama Lengkap:</strong> 
                <input type="text" id="fullName" value="<?= getSessionData('full_name'); ?>" readonly>
            </p>
            <p><strong>Nama Warmindo:</strong> 
                <input type="text" id="warmindoName" value="<?= getSessionData('warmindo_name'); ?>" readonly>
            </p>
            <p><strong>Email:</strong> 
                <input type="email" id="email" value="<?= getSessionData('email'); ?>" readonly>
            </p>
        </div>
        
        <button class="btn exit">Keluar</button>
    </div>
    <script src="pengaturan.js"></script>
</body>
</html>
