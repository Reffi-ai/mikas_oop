<?php
session_start();
include 'config.php';
include 'functions.php';

// Gunakan class UserManager
$userManager = new UserManager($conn);

// Membersihkan input dari karakter berbahaya (menghindari XSS).
function sanitizeInput(string $input): string {
    return htmlspecialchars($input);
}

// Proses registrasi dengan OOP
function handleRegistration($userManager) {
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        return;
    }

    $fullName = sanitizeInput($_POST['full_name']);
    $warmindoName = sanitizeInput($_POST['warmindo_name']);
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];

    // Cek apakah email sudah terdaftar
    if ($userManager->getUserByEmail($email)) {
        echo "Email sudah terdaftar! Silakan gunakan email lain.";
        return;
    }

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Simpan user baru dengan method OOP
    $success = $userManager->registerUser($fullName, $warmindoName, $email, $hashedPassword);

    if ($success) {
        // Set session data menggunakan method UserManager
        $_SESSION['full_name'] = $fullName;
        $_SESSION['warmindo_name'] = $warmindoName;
        $_SESSION['email'] = $email;
        header("Location: login.html");
        exit();
    } else {
        echo "Terjadi kesalahan saat registrasi.";
    }
}

handleRegistration($userManager);
$conn->close();
?>