<?php
require 'functions.php';
session_start();

// Fungsi OOP untuk menghapus transaksi berdasarkan array ID
function hapusTransaksiOOP(PDO $pdo, $userId, array $ids): bool {
    if (empty($ids)) {
        return false;
    }
    $transaksi = new Transaksi($pdo, $userId);

    // Buat query dinamis untuk menghapus hanya milik user terkait
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $query = "DELETE FROM transaksi WHERE id IN ($placeholders) AND user_id = ?";
    $stmt = $pdo->prepare($query);

    // Gabungkan $ids dan $userId untuk binding parameter
    $params = array_merge($ids, [$userId]);
    return $stmt->execute($params);
}

function hasilHapus(bool $berhasil): string {
    return $berhasil ? "Data berhasil dihapus." : "Terjadi kesalahan saat menghapus data.";
}

function redirectKe(string $location): void {
    header("Location: $location");
    exit;
}

// Main Execution
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ids = $_POST['ids'] ?? [];
    $userId = $_SESSION['user_id'] ?? null;

    if (!empty($ids) && $userId) {
        $berhasil = hapusTransaksiOOP($pdo, $userId, $ids);
        $_SESSION['success_message'] = hasilHapus($berhasil);
    } else {
        $_SESSION['error_message'] = "Tidak ada data yang dipilih atau user belum login.";
    }
}

redirectKe('laporan_keuangan.php');
?>