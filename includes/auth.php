<?php
/**
 * =====================================================
 * LAYANAN OTENTIKASI (AUTHENTICATION)
 * Menggunakan bcrypt Password Hashing
 * =====================================================
 * 
 * Library: Password Hashing (built-in PHP)
 * Algoritma: bcrypt
 * Fungsi: Memverifikasi identitas pemilih
 */

session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../crypto/DigitalSignature.php';

class Auth {
    private $db;
    
    public function __construct() {
        $this->db = getDBConnection();
    }
    
    /**
     * Registrasi user baru
     * 
     * @param string $nik NIK (16 digit)
     * @param string $namaLengkap Nama lengkap
     * @param string $email Email
     * @param string $password Password
     * @return array ['success' => bool, 'message' => string, 'private_key' => string]
     */
    public function register($nik, $namaLengkap, $email, $password) {
        try {
            // Validasi NIK
            if (!preg_match('/^\d{16}$/', $nik)) {
                return ['success' => false, 'message' => 'NIK harus 16 digit angka'];
            }
            
            // Validasi email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return ['success' => false, 'message' => 'Format email tidak valid'];
            }
            
            // Cek apakah NIK atau email sudah terdaftar
            $stmt = $this->db->prepare("SELECT id FROM users WHERE nik = ? OR email = ?");
            $stmt->execute([$nik, $email]);
            if ($stmt->fetch()) {
                return ['success' => false, 'message' => 'NIK atau Email sudah terdaftar'];
            }
            
            // Hash password dengan bcrypt (OTENTIKASI)
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
            
            // Generate HMAC key pair untuk digital signature (NON-REPUDIATION)
            $digitalSignature = new DigitalSignature();
            $keyPair = $digitalSignature->generateKeyPair();
            
            // Hash private key untuk verifikasi
            $privateKeyHash = hash('sha256', $keyPair['private_key']);
            
            // Simpan ke database
            $stmt = $this->db->prepare(
                "INSERT INTO users (nik, nama_lengkap, email, password, public_key, private_key_hash) 
                 VALUES (?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([
                $nik, 
                $namaLengkap, 
                $email, 
                $hashedPassword, 
                $keyPair['public_key'],
                $privateKeyHash
            ]);
            
            $userId = $this->db->lastInsertId();
            
            // Private key TIDAK disimpan di server untuk keamanan
            // User harus menyimpan sendiri private key yang diberikan
            
            return [
                'success' => true, 
                'message' => 'Registrasi berhasil! Simpan private key Anda dengan aman.',
                'user_id' => $userId,
                'private_key' => $keyPair['private_key']
            ];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Login user
     * 
     * @param string $email Email
     * @param string $password Password
     * @return array ['success' => bool, 'message' => string]
     */
    public function login($email, $password) {
        try {
            $stmt = $this->db->prepare(
                "SELECT id, nik, nama_lengkap, email, password, has_voted FROM users WHERE email = ?"
            );
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if (!$user) {
                return ['success' => false, 'message' => 'Email tidak ditemukan'];
            }
            
            // Verifikasi password menggunakan bcrypt (OTENTIKASI)
            if (!password_verify($password, $user['password'])) {
                // Log failed attempt
                $this->logAction($user['id'] ?? 0, 'LOGIN_FAILED', 'Invalid password');
                return ['success' => false, 'message' => 'Password salah'];
            }
            
            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_nik'] = $user['nik'];
            $_SESSION['user_name'] = $user['nama_lengkap'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['has_voted'] = $user['has_voted'];
            $_SESSION['logged_in'] = true;
            $_SESSION['login_time'] = time();
            
            // Log successful login
            $this->logAction($user['id'], 'LOGIN_SUCCESS', 'User logged in');
            
            return [
                'success' => true, 
                'message' => 'Login berhasil!',
                'user' => [
                    'id' => $user['id'],
                    'nama' => $user['nama_lengkap'],
                    'has_voted' => $user['has_voted']
                ]
            ];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Logout user
     */
    public function logout() {
        if (isset($_SESSION['user_id'])) {
            $this->logAction($_SESSION['user_id'], 'LOGOUT', 'User logged out');
        }
        
        session_unset();
        session_destroy();
        
        return ['success' => true, 'message' => 'Logout berhasil'];
    }
    
    /**
     * Cek apakah user sudah login
     */
    public function isLoggedIn() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
    
    /**
     * Cek apakah user sudah voting
     */
    public function hasVoted() {
        return isset($_SESSION['has_voted']) && $_SESSION['has_voted'] == 1;
    }
    
    /**
     * Get current user data
     */
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        return [
            'id' => $_SESSION['user_id'],
            'nik' => $_SESSION['user_nik'],
            'nama' => $_SESSION['user_name'],
            'email' => $_SESSION['user_email'],
            'has_voted' => $_SESSION['has_voted']
        ];
    }
    
    /**
     * Get user by ID
     */
    public function getUserById($userId) {
        $stmt = $this->db->prepare(
            "SELECT id, nik, nama_lengkap, email, public_key, has_voted, created_at FROM users WHERE id = ?"
        );
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }
    
    /**
     * Update status voting user
     */
    public function updateVotingStatus($userId) {
        $stmt = $this->db->prepare("UPDATE users SET has_voted = 1 WHERE id = ?");
        $stmt->execute([$userId]);
        $_SESSION['has_voted'] = 1;
    }
    
    /**
     * Log action untuk audit trail
     */
    public function logAction($userId, $action, $data = '') {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO vote_logs (user_id, action, ip_address, user_agent, data_hash) 
                 VALUES (?, ?, ?, ?, ?)"
            );
            
            $logData = [
                'action' => $action,
                'data' => $data,
                'timestamp' => date('Y-m-d H:i:s')
            ];
            
            $stmt->execute([
                $userId,
                $action,
                $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
                hash('sha256', json_encode($logData))
            ]);
        } catch (Exception $e) {
            // Silent fail for logging
        }
    }
    
    /**
     * Mendapatkan informasi otentikasi
     */
    public function getInfo() {
        return [
            'algorithm' => 'bcrypt',
            'cost_factor' => 12,
            'library' => 'PHP Password Hashing API',
            'purpose' => 'Otentikasi (Authentication)',
            'functions' => [
                'password_hash()' => 'Membuat hash password',
                'password_verify()' => 'Memverifikasi password'
            ]
        ];
    }
}

// Helper functions
function requireLogin() {
    $auth = new Auth();
    if (!$auth->isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

function requireNotVoted() {
    $auth = new Auth();
    if ($auth->hasVoted()) {
        header('Location: already_voted.php');
        exit();
    }
}
?>
