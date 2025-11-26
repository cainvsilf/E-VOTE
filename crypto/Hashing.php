<?php
/**
 * =====================================================
 * LAYANAN INTEGRITAS (INTEGRITY)
 * Menggunakan SHA-256 Hashing
 * =====================================================
 * 
 * Library: Hash (built-in PHP)
 * Algoritma: SHA-256
 * Fungsi: Memastikan data vote tidak dimodifikasi/diubah
 */

class Hashing {
    private $algorithm = 'sha256';
    
    /**
     * Hash data menggunakan SHA-256
     * 
     * @param string $data Data yang akan di-hash
     * @return string Hash dalam format hexadecimal
     */
    public function hash($data) {
        return hash($this->algorithm, $data);
    }
    
    /**
     * Hash dengan salt untuk keamanan tambahan
     * 
     * @param string $data Data yang akan di-hash
     * @param string $salt Salt untuk keamanan
     * @return string Hash dengan salt
     */
    public function hashWithSalt($data, $salt = null) {
        if ($salt === null) {
            $salt = bin2hex(random_bytes(16));
        }
        
        $hash = hash($this->algorithm, $salt . $data);
        
        return [
            'hash' => $hash,
            'salt' => $salt
        ];
    }
    
    /**
     * Verifikasi hash
     * 
     * @param string $data Data asli
     * @param string $expectedHash Hash yang diharapkan
     * @return bool True jika cocok
     */
    public function verify($data, $expectedHash) {
        $actualHash = $this->hash($data);
        return hash_equals($expectedHash, $actualHash);
    }
    
    /**
     * Verifikasi hash dengan salt
     * 
     * @param string $data Data asli
     * @param string $expectedHash Hash yang diharapkan
     * @param string $salt Salt yang digunakan
     * @return bool True jika cocok
     */
    public function verifyWithSalt($data, $expectedHash, $salt) {
        $actualHash = hash($this->algorithm, $salt . $data);
        return hash_equals($expectedHash, $actualHash);
    }
    
    /**
     * Buat hash untuk data vote
     * 
     * @param int $candidateId ID kandidat
     * @param int $userId ID pemilih
     * @param string $timestamp Waktu voting
     * @return string Hash vote
     */
    public function hashVote($candidateId, $userId, $timestamp) {
        $voteData = json_encode([
            'candidate_id' => $candidateId,
            'user_id' => $userId,
            'timestamp' => $timestamp
        ]);
        
        return $this->hash($voteData);
    }
    
    /**
     * Verifikasi integritas vote
     * 
     * @param int $candidateId ID kandidat
     * @param int $userId ID pemilih
     * @param string $timestamp Waktu voting
     * @param string $storedHash Hash yang tersimpan
     * @return bool True jika data tidak dimodifikasi
     */
    public function verifyVoteIntegrity($candidateId, $userId, $timestamp, $storedHash) {
        $calculatedHash = $this->hashVote($candidateId, $userId, $timestamp);
        return hash_equals($storedHash, $calculatedHash);
    }
    
    /**
     * Hash untuk audit trail
     * 
     * @param array $logData Data log
     * @return string Hash log
     */
    public function hashLog($logData) {
        return $this->hash(json_encode($logData));
    }
    
    /**
     * HMAC untuk verifikasi tambahan
     * 
     * @param string $data Data
     * @param string $key Secret key
     * @return string HMAC
     */
    public function hmac($data, $key) {
        return hash_hmac($this->algorithm, $data, $key);
    }
    
    /**
     * Mendapatkan informasi hashing
     */
    public function getInfo() {
        return [
            'algorithm' => strtoupper($this->algorithm),
            'output_length' => '256 bits (64 hex characters)',
            'library' => 'PHP Hash Extension',
            'purpose' => 'Integritas (Integrity)',
            'properties' => [
                'One-way function',
                'Collision resistant',
                'Deterministic'
            ]
        ];
    }
}
?>
