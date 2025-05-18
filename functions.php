<?php
require 'config.php';

// Base class untuk koneksi database
class Database {
    protected $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
}

// Class untuk transaksi
class Transaksi extends Database {
    // Properti private
    private $user_id;

    public function __construct($pdo, $user_id) {
        parent::__construct($pdo);
        $this->user_id = $user_id;
    }

    // Getter dan Setter untuk user_id
    public function getUserId() {
        return $this->user_id;
    }

    public function setUserId($user_id) {
        $this->user_id = $user_id;
    }

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

    public function hapusBerdasarkanDeskripsi($deskripsi) {
        $sql = "DELETE FROM transaksi WHERE user_id = :user_id AND deskripsi = :deskripsi";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':user_id' => $this->user_id,
            ':deskripsi' => $deskripsi
        ]);
    }
}

// Class untuk utang, inheritance dari Database
class Utang extends Database {
    private $user_id;
    private $transaksi;

    public function __construct($pdo, $user_id) {
        parent::__construct($pdo);
        $this->user_id = $user_id;
        $this->transaksi = new Transaksi($pdo, $user_id);
    }

    // Getter dan Setter untuk user_id
    public function getUserId() {
        return $this->user_id;
    }

    public function setUserId($user_id) {
        $this->user_id = $user_id;
        $this->transaksi->setUserId($user_id); // Sinkronkan juga ke Transaksi
    }

    public function ambilSemua() {
        $sql = "SELECT * FROM utang WHERE user_id = :user_id ORDER BY tanggal DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':user_id' => $this->user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function tandaiLunas($id) {
        $sql = "SELECT nama, keterangan FROM utang WHERE id = :id AND user_id = :user_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':id' => $id,
            ':user_id' => $this->user_id
        ]);
        $utang = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($utang) {
            // Tandai utang sebagai lunas
            $sql = "UPDATE utang SET status = 'Lunas' WHERE id = :id AND user_id = :user_id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':id' => $id,
                ':user_id' => $this->user_id
            ]);

            // Hapus transaksi dengan deskripsi yang sesuai
            $deskripsi = "Utang {$utang['nama']}: {$utang['keterangan']}";
            $this->transaksi->hapusBerdasarkanDeskripsi($deskripsi);
        }
    }

    public function totalPerPelanggan() {
        $sql = "SELECT nama, SUM(jumlah) AS total 
                FROM utang 
                WHERE user_id = :user_id AND status != 'Lunas' 
                GROUP BY nama";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':user_id' => $this->user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function hapusLunas($id) {
        $sql = "DELETE FROM utang WHERE id = :id AND user_id = :user_id AND status = 'Lunas'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':id' => $id,
            ':user_id' => $this->user_id
        ]);
    }

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

        // Catat pengeluaran terkait utang ke tabel transaksi
        $this->transaksi->catat('pengeluaran', $jumlah, "Utang $nama: $keterangan");
    }
}

// login.php
class UserManager {
    private $conn;
    public function __construct($conn) { $this->conn = $conn; }
    public function getUserByEmail($email) {
        $stmt = $this->conn->prepare("SELECT id, full_name, warmindo_name, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->num_rows > 0 ? $result->fetch_assoc() : null;
        $stmt->close();
        return $user;
    }
    public function verifyPassword($inputPassword, $hashedPassword) {
        return password_verify($inputPassword, $hashedPassword);
    }
    public function setSessionData($user, $email) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['warmindo_name'] = $user['warmindo_name'];
        $_SESSION['email'] = $email;
    }

    public function registerUser($fullName, $warmindoName, $email, $hashedPassword) {
        $stmt = $this->conn->prepare("INSERT INTO users (full_name, warmindo_name, email, password) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $fullName, $warmindoName, $email, $hashedPassword);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }
}