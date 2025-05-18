<?php

if (!function_exists('createMysqliConnection')) {
    function createMysqliConnection(
        string $servername,
        string $username,
        string $password,
        string $dbname
    ): mysqli {
        $conn = new mysqli($servername, $username, $password, $dbname);

        if ($conn->connect_error) {
            error_log("Koneksi gagal: " . $conn->connect_error);
            throw new Exception("Koneksi MySQLi gagal.");
        }

        return $conn;
    }
}

if (!function_exists('createPdoConnection')) {
    function createPdoConnection(
        string $servername,
        string $username,
        string $password,
        string $dbname
    ): PDO {
        try {
            $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        } catch (PDOException $e) {
            throw new Exception("Koneksi PDO gagal: " . $e->getMessage());
        }
    }
}

if (!function_exists('getDatabaseConfig')) {
    // Konfigurasi diambil dari environment atau default
    function getDatabaseConfig(): array {
        return [
            'servername' => getenv('DB_SERVER') ?: 'localhost',
            'username'   => getenv('DB_USERNAME') ?: 'root',
            'password'   => getenv('DB_PASSWORD') ?: '',
            'dbname'     => getenv('DB_NAME') ?: 'mikass',
        ];
    }
}

if (!function_exists('connect_config')) {
    function connect_config(): mysqli {
        return createMysqliConnection(...array_values(getDatabaseConfig()));
    }
}

// --- Bagian ini WAJIB agar $pdo dan $conn tersedia untuk OOP dan procedural ---
$config = getDatabaseConfig();

try {
    // Variabel global untuk seluruh aplikasi
    $conn = createMysqliConnection(
        $config['servername'],
        $config['username'],
        $config['password'],
        $config['dbname']
    );

    $pdo = createPdoConnection(
        $config['servername'],
        $config['username'],
        $config['password'],
        $config['dbname']
    );
} catch (Exception $e) {
    error_log($e->getMessage());
    die("Koneksi database gagal. Silakan coba lagi nanti.");
}