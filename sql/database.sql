-- =====================================================
-- E-VOTE PEMILU - Database Schema
-- Aplikasi E-Voting dengan 4 Layanan Kriptografi
-- =====================================================

CREATE DATABASE IF NOT EXISTS evote_pemilu;
USE evote_pemilu;

-- Tabel Users (Pemilih)
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nik VARCHAR(16) UNIQUE NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,  -- Hashed dengan bcrypt (Otentikasi)
    public_key TEXT,                  -- RSA Public Key (Non-Repudiation)
    private_key_hash VARCHAR(64),     -- Hash dari private key untuk verifikasi
    has_voted TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel Candidates (Kandidat/Paslon)
CREATE TABLE candidates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nomor_urut INT UNIQUE NOT NULL,
    nama_paslon VARCHAR(200) NOT NULL,
    nama_capres VARCHAR(100) NOT NULL,
    nama_cawapres VARCHAR(100) NOT NULL,
    partai_pengusung VARCHAR(255),
    visi TEXT,
    misi TEXT,
    foto VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Votes (Suara Terenkripsi)
CREATE TABLE votes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    encrypted_vote TEXT NOT NULL,         -- Vote terenkripsi AES-256 (Kerahasiaan)
    vote_hash VARCHAR(64) NOT NULL,       -- Hash SHA-256 dari vote (Integritas)
    digital_signature TEXT NOT NULL,      -- Tanda tangan digital RSA (Non-Repudiation)
    iv VARCHAR(32) NOT NULL,              -- Initialization Vector untuk AES
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Tabel Vote Logs (Audit Trail)
CREATE TABLE vote_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    action VARCHAR(50) NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    data_hash VARCHAR(64),
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Tabel Admin
CREATE TABLE admins (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nama VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert Sample Candidates (Paslon)
INSERT INTO candidates (nomor_urut, nama_paslon, nama_capres, nama_cawapres, partai_pengusung, visi, misi) VALUES
(1, 'Pasangan Nomor 1', 'Dr. Ahmad Wijaya', 'Ir. Siti Rahayu', 'Koalisi Merah Putih', 
   'Indonesia Maju, Sejahtera, dan Berkeadilan',
   'Meningkatkan pendidikan, kesehatan, dan kesejahteraan rakyat'),
(2, 'Pasangan Nomor 2', 'Prof. Budi Santoso', 'Dr. Maya Kusuma', 'Koalisi Nusantara Bersatu',
   'Indonesia Emas 2045',
   'Membangun infrastruktur, ekonomi digital, dan pemberdayaan UMKM'),
(3, 'Pasangan Nomor 3', 'H. Rizki Pratama', 'Hj. Dewi Lestari', 'Koalisi Rakyat Berdaulat',
   'Indonesia Berdaulat dan Bermartabat',
   'Pemberantasan korupsi, penegakan hukum, dan kemandirian ekonomi');

-- Insert Default Admin
INSERT INTO admins (username, password, nama) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator');
-- Password default: password

-- Index untuk optimasi
CREATE INDEX idx_votes_user ON votes(user_id);
CREATE INDEX idx_votes_hash ON votes(vote_hash);
CREATE INDEX idx_users_nik ON users(nik);
CREATE INDEX idx_logs_user ON vote_logs(user_id);
