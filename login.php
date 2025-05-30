<?php
session_start();
include 'config.php';
include 'functions.php'; // pastikan ini sudah ada

// Inisialisasi objek UserManager
$userManager = new UserManager($pdo);

// Fungsi untuk menangani login dengan OOP
function handleLogin($userManager, $email, $password) {
    $user = $userManager->getUserByEmail($email);

    if (!$user) {
        return "Email tidak ditemukan!";
    }

    if (!$userManager->verifyPassword($password, $user['password'])) {
        return "Password salah!";
    }

    $userManager->setSessionData($user, $email);
    redirectToDashboard();
}

// Mengarahkan pengguna ke halaman dasboard.php setelah login sukses.
function redirectToDashboard() {
    header("Location: dasboard.php");
    exit();
}

// Fungsi utama untuk menjalankan login
function processLogin($userManager) {
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        return;
    }

    $email = htmlspecialchars($_POST['email']);
    $password = $_POST['password'];

    $error = handleLogin($userManager, $email, $password);

    if ($error) {
        echo $error;
    }
}

// Jalankan proses login
processLogin($userManager);

// Menutup koneksi database setelah selesai.
// $conn->close();
?>