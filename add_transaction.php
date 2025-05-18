<?php
require 'functions.php';

// Fungsi ini menangani request yang dikirim dari form (melalui method POST) dan juga data dari sesi login.
function handleRequest($pdo, $postData, $sessionData)
{
    if (
        isset($postData["tipe"], $postData["jumlah"], $postData["deskripsi"]) &&
        isset($sessionData['user_id'])
    ) {
        $userId = $sessionData['user_id'];
        $tipe = $postData['tipe'];
        $jumlah = $postData['jumlah'];
        $deskripsi = $postData['deskripsi'];

        // Gunakan class Transaksi OOP
        $transaksi = new Transaksi($pdo, $userId);
        $result = $transaksi->catat($tipe, $jumlah, $deskripsi);

        if ($result['success']) {
            return [
                'redirect' => 'laporan_keuangan.php',
                'success_message' => $result['message'],
                'error_message' => null,
            ];
        } else {
            return [
                'redirect' => null,
                'success_message' => null,
                'error_message' => $result['message'],
            ];
        }
    }

    return [
        'redirect' => null,
        'success_message' => null,
        'error_message' => 'Data tidak lengkap atau user belum login',
    ];
}

session_start();
$response = handleRequest($pdo, $_POST, $_SESSION);

if ($response['redirect']) {
    $_SESSION['success_message'] = $response['success_message'];
    header("Location: " . $response['redirect']);
    exit;
} else {
    if ($response['error_message']) {
        echo $response['error_message'];
    }
}
?>