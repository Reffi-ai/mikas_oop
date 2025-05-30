<?php
require 'functions.php';
session_start();

// Fungsi OOP untuk menghapus transaksi berdasarkan array ID
function hapusTransaksiOOP(PDO $pdo, $userId, array $ids): bool {
    if (empty($ids)) {
        return false;
    }
    $transaksi = new Transaksi($pdo, $userId);

    // Ambil deskripsi transaksi yang akan dihapus
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $querySelect = "SELECT deskripsi FROM transaksi WHERE id IN ($placeholders) AND user_id = ?";
    $stmtSelect = $pdo->prepare($querySelect);
    $paramsSelect = array_merge($ids, [$userId]);
    $stmtSelect->execute($paramsSelect);
    $deskripsiList = $stmtSelect->fetchAll(PDO::FETCH_COLUMN);

    // Tandai utang lunas jika deskripsi sesuai format utang
    $utang = new Utang($pdo, $userId);
    foreach ($deskripsiList as $deskripsi) {
        if (strpos($deskripsi, 'Utang ') === 0) {
            $utang->tandaiLunasByDeskripsi($deskripsi);
        }
    }

    // Hapus transaksi
    $query = "DELETE FROM transaksi WHERE id IN ($placeholders) AND user_id = ?";
    $stmt = $pdo->prepare($query);
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
        $_SESSION['error_message'] = "Tidak ada data yang dipilih.";
    }
}

redirectKe('laporan_keuangan.php');
?>