<?php
/**
 * =====================================================
 * E-VOTE PEMILU - Database Configuration
 * =====================================================
 */

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'evote_pemilu');

// Encryption Key untuk AES-256 (Kerahasiaan)
define('ENCRYPTION_KEY', 'EVotePemilu2024SecretKey32Bytes!');

// Koneksi Database menggunakan PDO
function getDBConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        return new PDO($dsn, DB_USER, DB_PASS, $options);
    } catch (PDOException $e) {
        die("Koneksi database gagal: " . $e->getMessage());
    }
}

// Koneksi menggunakan MySQLi (alternatif)
function getMySQLiConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die("Koneksi gagal: " . $conn->connect_error);
    }
    $conn->set_charset("utf8mb4");
    return $conn;
}
?>
