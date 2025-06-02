<?php
require 'config.php';

// Menangani seluruh manajemen pengguna, seperti login, registrasi, verifikasi password, dan pengaturan sesi (session).
class UserManager {
    private $pdo; // Objek PDO untuk koneksi database, disimpan sebagai properti kelas agar bisa digunakan di seluruh method.

    // Menyimpan objek PDO ke property protected $pdo yang digunakan untuk koneksi dan query ke database.
    public function __construct($pdo) { // cons waktu pemanggilan otomatis saat membuat objek dari kelas ini.
        $this->pdo = $pdo;
    }

    // Mengambil data pengguna berdasarkan email, untuk login atau validasi.
    public function getUserByEmail($email) { //$email adalah parameter method, bukan properti class. Parameter secara otomatis menjadi variabel lokal yang bisa langsung digunakan di dalam method tersebut.
        $stmt = $this->pdo->prepare("SELECT id, full_name, warmindo_name, password FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    // Mengecek apakah password yang dimasukkan cocok dengan hash-nya.
    public function verifyPassword($inputPassword, $hashedPassword) {
        return password_verify($inputPassword, $hashedPassword);
    }

    // Menyimpan data user ke dalam session setelah login berhasil.
    public function setSessionData($user, $email) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['warmindo_name'] = $user['warmindo_name'];
        $_SESSION['email'] = $email;
    }

    // Menambahkan pengguna baru ke database saat registrasi.
    public function registerUser($fullName, $warmindoName, $email, $hashedPassword) {
        $stmt = $this->pdo->prepare("INSERT INTO users (full_name, warmindo_name, email, password) VALUES (:full_name, :warmindo_name, :email, :password)");
        return $stmt->execute([
            ':full_name' => $fullName,
            ':warmindo_name' => $warmindoName,
            ':email' => $email,
            ':password' => $hashedPassword
        ]);
    }
}

// Kelas induk dasar untuk menyimpan koneksi database ($pdo) dan digunakan oleh kelas Utang dan Transaksi.
class Database {
    protected $pdo; // protected agar dapat diakses oleh kelas turunan

    // Menyimpan objek PDO ke property protected $pdo yang digunakan untuk koneksi dan query ke database.
    public function __construct($pdo) { 
        $this->pdo = $pdo; 
    }
}

// Mengelola data utang pelanggan, termasuk mencatat, menampilkan, menandai lunas, dan menghapus.
class Utang extends Database {
    private $user_id; // ID pengguna saat ini.
    private $transaksi; // properti yang menyimpan objek dari kelas Transaksi, digunakan untuk mencatat pengeluaran saat utang ditambahkan.

    public function __construct($pdo, $user_id) {
        parent::__construct($pdo); // Memanggil konstruktor dari kelas Database untuk menyimpan koneksi PDO.
        $this->user_id = $user_id;  // Menyimpan ID pengguna (user_id) ke properti objek.
        $this->transaksi = new Transaksi($pdo, $user_id); // Membuat objek dari class Transaksi dan menyimpannya ke properti $this->transaksi.
    }

    // Mengambil semua utang yang dimiliki pengguna, diurutkan berdasarkan tanggal.
    public function ambilSemua() {
        $sql = "SELECT * FROM utang WHERE user_id = :user_id ORDER BY tanggal DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':user_id' => $this->user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Menambahkan utang baru ke database, mencatat pengeluaran, dan mengatur status awal sebagai 'Belum Lunas'.
    public function tambah($nama, $jumlah, $keterangan) {
        $sql = "INSERT INTO utang (user_id, nama, jumlah, keterangan, status, tanggal) 
                VALUES (:user_id, :nama, :jumlah, :keterangan, 'Belum Lunas', NOW())";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':user_id' => $this->user_id,
            ':nama' => $nama,
            ':jumlah' => $jumlah,
            ':keterangan' => $keterangan
        ]);
        $this->transaksi->catat('pengeluaran', $jumlah, "Utang $nama: $keterangan");
    }

    // Menjumlahkan total utang yang belum lunas berdasarkan ID pengguna.
    public function totalPerPelanggan() {
        $sql = "SELECT nama, SUM(jumlah) AS total 
                FROM utang 
                WHERE user_id = :user_id AND status != 'Lunas' 
                GROUP BY nama";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':user_id' => $this->user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Mengubah status utang menjadi 'Lunas' berdasarkan ID utang. ditandai pada saat di halaman utang_index.php.
    public function tandaiLunas($id) {
        $sql = "SELECT nama, keterangan FROM utang WHERE id = :id AND user_id = :user_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':id' => $id,
            ':user_id' => $this->user_id
        ]);
        $utang = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($utang) {
            $sql = "UPDATE utang SET status = 'Lunas' WHERE id = :id AND user_id = :user_id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':id' => $id,
                ':user_id' => $this->user_id
            ]);
            $deskripsi = "Utang {$utang['nama']}: {$utang['keterangan']}";
            $this->transaksi->hapusBerdasarkanDeskripsi($deskripsi);
        }
    }

    // Menghapus utang berdasarkan ID, hanya jika statusnya 'Lunas'.
    public function hapusLunas($id) {
        $sql = "DELETE FROM utang WHERE id = :id AND user_id = :user_id AND status = 'Lunas'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':id' => $id,
            ':user_id' => $this->user_id
        ]);
    }

    // Menandai utang sebagai lunas berdasarkan deskripsi yang diberikan. ditandai pada saat di halaman laporan_keuangan.php.
    public function tandaiLunasByDeskripsi($deskripsi) {
        $sql = "UPDATE utang SET status = 'Lunas' WHERE user_id = :user_id AND CONCAT('Utang ', nama, ': ', keterangan) = :deskripsi";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':user_id' => $this->user_id,
            ':deskripsi' => $deskripsi
        ]);
    }
}

// Mencatat semua transaksi keuangan (pemasukan dan pengeluaran) pengguna, serta menghitung totalnya.
class Transaksi extends Database {
    private $user_id; // ID pengguna saat ini.

    public function __construct($pdo, $user_id) {
        parent::__construct($pdo); // Memanggil konstruktor dari kelas Database untuk menyimpan koneksi PDO.
        $this->user_id = $user_id;  // Menyimpan ID pengguna (user_id) ke properti objek.
    }

    // Mengambil semua transaksi pengguna berdasarkan ID, diurutkan berdasarkan tanggal.
    public function getAll() {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT * FROM transaksi WHERE user_id = :user_id ORDER BY tanggal DESC"
            );
            $stmt->execute([':user_id' => $this->user_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    // Menyimpan transaksi baru ke tabel transaksi (baik pemasukan maupun pengeluaran).
    public function catat($tipe, $jumlah, $deskripsi) {
        try {
            $stmt = $this->pdo->prepare(
                "INSERT INTO transaksi (user_id, tipe, jumlah, deskripsi, tanggal) 
                VALUES (:user_id, :tipe, :jumlah, :deskripsi, NOW())"
            );
            $stmt->execute([
                ':user_id'   => $this->user_id,
                ':tipe'      => $tipe,
                ':jumlah'    => $jumlah,
                ':deskripsi' => $deskripsi
            ]);
            return [
                'success' => true,
                'message' => 'Transaksi berhasil ditambahkan!'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    // Menjumlahkan seluruh pemasukan user berdasarkan ID pengguna.
    public function totalPemasukan() {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT SUM(jumlah) AS total FROM transaksi 
                WHERE tipe = 'pemasukan' AND user_id = :user_id"
            );
            $stmt->execute([':user_id' => $this->user_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }

    // Menjumlahkan seluruh pengeluaran user berdasarkan ID pengguna.
    public function totalPengeluaran() {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT SUM(jumlah) AS total FROM transaksi 
                WHERE tipe = 'pengeluaran' AND user_id = :user_id"
            );
            $stmt->execute([':user_id' => $this->user_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }
    
    // Menghapus transaksi berdasarkan deskripsi (biasanya saat utang dilunasi).
    public function hapusBerdasarkanDeskripsi($deskripsi) {
        $sql = "DELETE FROM transaksi WHERE user_id = :user_id AND deskripsi = :deskripsi";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':user_id' => $this->user_id,
            ':deskripsi' => $deskripsi
        ]);
    }
}