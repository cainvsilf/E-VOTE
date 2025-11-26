<?php
/**
 * Database Configuration
 * Copy file ini ke database.php dan sesuaikan dengan konfigurasi Anda
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'evote_pemilu');
define('DB_USER', 'root');
define('DB_PASS', ''); // Ganti dengan password database Anda

// Encryption key untuk AES-256 (Kerahasiaan)
define('ENCRYPTION_KEY', 'your-secret-encryption-key-here-change-this');

/**
 * Membuat koneksi database menggunakan PDO
 */
function getDBConnection() {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
        return $pdo;
    } catch (PDOException $e) {
        die("Koneksi database gagal: " . $e->getMessage());
    }
}
?>
