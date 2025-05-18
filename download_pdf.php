<?php
require 'functions.php';
require 'vendor/autoload.php'; // Pastikan menggunakan library seperti Dompdf

use Dompdf\Dompdf;

session_start();

// Fungsi untuk memastikan user sudah login
function userHarusLogin(): int {
    if (!isset($_SESSION['user_id'])) {
        die("Error: Anda harus login terlebih dahulu.");
    }
    return $_SESSION['user_id'];
}

// Fungsi untuk membuat konten HTML dari data transaksi
function buatHtmlLaporan(array $transaksi, int $totalPemasukan, int $totalPengeluaran, int $saldoAkhir, string $css): string {
    $rows = array_map(fn($item) => '
        <tr>
            <td>' . htmlspecialchars($item['tanggal']) . '</td>
            <td>' . ucfirst(htmlspecialchars($item['tipe'])) . '</td>
            <td>Rp' . number_format($item['jumlah'], 0, ',', '.') . '</td>
            <td>' . htmlspecialchars($item['deskripsi']) . '</td>
        </tr>', $transaksi);

    $rowsHtml = !empty($rows) ? implode('', $rows) : '
        <tr>
            <td colspan="4" style="text-align: center;">Tidak ada data transaksi.</td>
        </tr>';

    return '
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <title>Laporan Keuangan</title>
        <style>' . $css . '</style>
    </head>
    <body>
        <h2>Laporan Keuangan</h2>
        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Tipe</th>
                    <th>Jumlah</th>
                    <th>Deskripsi</th>
                </tr>
            </thead>
            <tbody>' . $rowsHtml . '</tbody>
        </table>
        <div class="summary">
            <div class="summary pemasukan"><strong>Total Pemasukan:</strong> Rp' . number_format($totalPemasukan, 0, ',', '.') . '</div>
            <div class="summary pengeluaran"><strong>Total Pengeluaran:</strong> Rp' . number_format($totalPengeluaran, 0, ',', '.') . '</div>
            <div class="summary total"><strong>Saldo Akhir:</strong> Rp' . number_format($saldoAkhir, 0, ',', '.') . '</div>
        </div>
    </body>
    </html>';
}

// Fungsi untuk membuat dan mengirim PDF
function buatDanKirimPdf(string $html, string $namaFile): void {
    $dompdf = new Dompdf();
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $dompdf->stream($namaFile, ["Attachment" => true]);
}

// Eksekusi utama dengan OOP
$user_id = userHarusLogin();
$transaksiObj = new Transaksi($pdo, $user_id);
$transaksi = $transaksiObj->getAll();
$totalPemasukan = $transaksiObj->totalPemasukan();
$totalPengeluaran = $transaksiObj->totalPengeluaran();
$saldoAkhir = $totalPemasukan - $totalPengeluaran;
$css = file_get_contents('download_pdf.css');
$html = buatHtmlLaporan($transaksi, $totalPemasukan, $totalPengeluaran, $saldoAkhir, $css);
buatDanKirimPdf($html, "laporan_keuangan.pdf");
?>