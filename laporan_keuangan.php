<?php
session_start();
require 'config.php';
require 'functions.php';

// Ambil user_id dari sesi
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    die("Error: Anda harus login terlebih dahulu."); // Berikan pesan error jika user_id tidak ditemukan
}

// Inisialisasi objek Transaksi
$transaksiObj = new Transaksi($pdo, $user_id);

// Hitung total pemasukan, pengeluaran, dan saldo akhir menggunakan OOP
$totalPemasukan = $transaksiObj->totalPemasukan();
$totalPengeluaran = $transaksiObj->totalPengeluaran();
$saldoAkhir = $totalPemasukan - $totalPengeluaran;
$transaksi = $transaksiObj->getAll(); // mengambil semua transaksi (termasuk utang) dari database berdasarkan user_id.
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Keuangan</title>
    <link rel="stylesheet" href="laporan_keuangan.css">
</head>
<body class="bg-light">
    <div class="container">
        <a href="dasboard.php" class="btn kembali kuning">&#8617; Kembali</a>
        <h2 class="text-center">Laporan Keuangan</h2>
        <!-- Notifikasi -->
        <?php if (isset($_SESSION['success_message'])) : ?>
            <div id="success-alert" class="alert alert-success alert-dismissible position-fixed top-0 end-0 m-3 shadow" role="alert">
                <strong>Berhasil</strong> <?= $_SESSION['success_message']; ?>
                <button type="button" class="btn-close" onclick="this.parentElement.style.display='none';" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])) : ?>
            <div id="error-alert" class="alert alert-danger alert-dismissible position-fixed top-0 end-0 m-3 shadow" role="alert">
                <strong>Gagal</strong> <?= $_SESSION['error_message']; ?>
                <button type="button" class="btn-close" onclick="this.parentElement.style.display='none';" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>
        
        <form method="post" action="delete_multiple.php">
            <div class="button-group">
                <button type="submit" class="btn delete merah" onclick="return confirm('Apakah Anda yakin ingin menghapus data yang dipilih?')">🗑️ Hapus Data Terpilih</button>
                <a href="download_pdf.php" class="btn Download-PDF biru">📄 Download PDF</a>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                        <th class="checkbox-column"><input type="checkbox" id="select-all" onclick="toggleCheckboxes(this)"></th>                        <th>Tanggal</th>
                            <th>Tipe</th>
                            <th>Jumlah</th>
                            <th>Deskripsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($transaksi)) : ?>
                            <?php foreach ($transaksi as $item) : ?>
                                <tr>
                                    <td><input type="checkbox" name="ids[]" value="<?= $item['id'] ?>"></td>
                                    <td><?= $item['tanggal'] ?></td>
                                    <td><?= ucfirst($item['tipe']) ?></td>
                                    <td>Rp<?= number_format($item['jumlah'], 0, ',', '.') ?></td>
                                    <td><?= $item['deskripsi'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="5" style="text-align: center;">Tidak ada data transaksi.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </form>
        <div class="summary">
            <div class="card hijau">
                <h3>Total Pemasukan</h3>
                <p>Rp<?= number_format($totalPemasukan, 0, ',', '.') ?></p>
            </div>
            <div class="card merah">
                <h3>Total Pengeluaran</h3>
                <p>Rp<?= number_format($totalPengeluaran, 0, ',', '.') ?></p>
            </div>
            <div class="card biru">
                <h3>Saldo Akhir</h3>
                <p>Rp<?= number_format($saldoAkhir, 0, ',', '.') ?></p>
            </div>
        </div>
        <a href="add_transaction.html" class="tambah-transaksi kuning">&#128722; Tambah Transaksi</a>
    </div>
    <script src="laporan_keuangan.js"></script>
</body>
</html>