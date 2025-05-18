<?php
require 'functions.php'; 
session_start(); // Memulai sesi untuk menyimpan data pengguna yang sudah login

// untuk memastikan pengguna sudah login:
function validasiLogin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
    return $_SESSION['user_id'];
}

// Ambil user_id dari sesi login
$user_id = validasiLogin(); // dipanggil untuk memastikan pengguna telah login, lalu user_id disimpan ke $user_id.

// Buat objek Transaksi
$transaksi = new Transaksi($pdo, $user_id);

// Ambil total pemasukan dan pengeluaran menggunakan method OOP
$totalPemasukan = $transaksi->totalPemasukan();
$totalPengeluaran = $transaksi->totalPengeluaran();
$saldoAkhir = $totalPemasukan - $totalPengeluaran;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Keuangan</title>
    <link rel="stylesheet" href="dasboard.css"> <!-- Sudah diperbaiki nama file -->
</head>
<body>
    <div class="dashboard">
        <main class="content">
            <header>
                <h1><span class="highlight">MIEKAS</span><span class="miee"> - Mie Kenyal Keuangan Digital</span></h1>
            </header>

            <section class="menu-aplikasi">
                <h2>Menu Aplikasi</h2>
                <p>Kelola transaksi dan atur keuangan bisnis Anda</p>
                <div class="menu-buttons">
                    <a href="utang_index.php" class="btn utang">Utang Pelanggan</a>
                    <a href="laporan_keuangan.php" class="btn orange">Laporan Keuangan</a>
                    <a href="pengaturan.php" class="btn green">Pengaturan Akun</a>
                </div>
            </section>

            <section class="ringkasan-keuangan">
                <h2>Ringkasan Keuangan</h2>
                <p>Tinjau pemasukan, pengeluaran, dan saldo akhir dalam satu tampilan.</p>

                <div class="summary">
                    <div class="card green">
                        <h3>Total Pemasukan</h3>
                        <p>Rp<?= number_format($totalPemasukan, 0, ',', '.') ?></p>
                    </div>
                    <div class="card red">
                        <h3>Total Pengeluaran</h3>
                        <p>Rp<?= number_format($totalPengeluaran, 0, ',', '.') ?></p>
                    </div>
                    <div class="card blue">
                        <h3>Saldo Akhir</h3>
                        <p>Rp<?= number_format($saldoAkhir, 0, ',', '.') ?></p>
                    </div>
                </div>
            </section>
        </main>
    </div>
</body>
</html>