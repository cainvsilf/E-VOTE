<?php
/**
 * =====================================================
 * LAYANAN KERAHASIAAN (CONFIDENTIALITY)
 * Menggunakan AES-256-CBC Encryption
 * =====================================================
 * 
 * Library: OpenSSL (built-in PHP)
 * Algoritma: AES-256-CBC
 * Fungsi: Mengenkripsi data suara agar tidak bisa dibaca pihak tidak berwenang
 */

class Encryption {
    private $method = 'AES-256-CBC';
    private $key;
    
    public function __construct($key = null) {
        $this->key = $key ?? ENCRYPTION_KEY;
        // Pastikan key memiliki panjang yang tepat (32 bytes untuk AES-256)
        $this->key = hash('sha256', $this->key, true);
    }
    
    /**
     * Enkripsi data menggunakan AES-256-CBC
     * 
     * @param string $plaintext Data yang akan dienkripsi
     * @return array ['ciphertext' => '...', 'iv' => '...']
     */
    public function encrypt($plaintext) {
        // Generate random IV (Initialization Vector)
        $ivLength = openssl_cipher_iv_length($this->method);
        $iv = openssl_random_pseudo_bytes($ivLength);
        
        // Enkripsi menggunakan AES-256-CBC
        $ciphertext = openssl_encrypt(
            $plaintext,
            $this->method,
            $this->key,
            OPENSSL_RAW_DATA,
            $iv
        );
        
        return [
            'ciphertext' => base64_encode($ciphertext),
            'iv' => base64_encode($iv)
        ];
    }
    
    /**
     * Dekripsi data yang telah dienkripsi
     * 
     * @param string $ciphertext Data terenkripsi (base64)
     * @param string $iv Initialization Vector (base64)
     * @return string Data asli (plaintext)
     */
    public function decrypt($ciphertext, $iv) {
        $ciphertext = base64_decode($ciphertext);
        $iv = base64_decode($iv);
        
        $plaintext = openssl_decrypt(
            $ciphertext,
            $this->method,
            $this->key,
            OPENSSL_RAW_DATA,
            $iv
        );
        
        return $plaintext;
    }
    
    /**
     * Enkripsi data vote
     * 
     * @param int $candidateId ID kandidat yang dipilih
     * @param int $userId ID pemilih
     * @param string $timestamp Waktu voting
     * @return array Data terenkripsi dengan IV
     */
    public function encryptVote($candidateId, $userId, $timestamp) {
        $voteData = json_encode([
            'candidate_id' => $candidateId,
            'user_id' => $userId,
            'timestamp' => $timestamp,
            'nonce' => bin2hex(random_bytes(16)) // Tambahan random untuk keamanan
        ]);
        
        return $this->encrypt($voteData);
    }
    
    /**
     * Dekripsi data vote
     * 
     * @param string $encryptedVote Vote terenkripsi
     * @param string $iv Initialization Vector
     * @return array|false Data vote atau false jika gagal
     */
    public function decryptVote($encryptedVote, $iv) {
        $decrypted = $this->decrypt($encryptedVote, $iv);
        
        if ($decrypted === false) {
            return false;
        }
        
        return json_decode($decrypted, true);
    }
    
    /**
     * Mendapatkan informasi enkripsi
     */
    public function getInfo() {
        return [
            'algorithm' => $this->method,
            'key_length' => '256 bits',
            'mode' => 'CBC (Cipher Block Chaining)',
            'library' => 'OpenSSL',
            'purpose' => 'Kerahasiaan (Confidentiality)'
        ];
    }
}
?>
