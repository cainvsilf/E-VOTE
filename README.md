# E-Vote Pemilu 🗳️

Aplikasi E-Voting sederhana yang mendemonstrasikan **4 layanan kriptografi**:

1. **Otentikasi (Authentication)** - bcrypt
2. **Integritas (Integrity)** - SHA-256
3. **Kerahasiaan (Confidentiality)** - AES-256-CBC
4. **Anti-Penyangkalan (Non-Repudiation)** - HMAC-SHA256 Digital Signature

## 📋 Requirements

- PHP 7.4+ atau PHP 8.x
- MySQL / MariaDB
- XAMPP (atau web server lain dengan PHP & MySQL)

## 🚀 Instalasi

### 1. Clone Repository
```bash
git clone https://github.com/cainvsilf/E-VOTE.git
cd E-VOTE
```

### 2. Setup Database
- Buka phpMyAdmin
- Import file `sql/database.sql`
- Atau jalankan query di file tersebut secara manual

### 3. Konfigurasi Database
```bash
cp config/database.example.php config/database.php
```
Edit `config/database.php` dan sesuaikan:
- `DB_HOST` - Host database (default: localhost)
- `DB_NAME` - Nama database (default: evote_pemilu)
- `DB_USER` - Username database (default: root)
- `DB_PASS` - Password database
- `ENCRYPTION_KEY` - Kunci enkripsi AES-256 (ganti dengan key yang aman)

### 4. Akses Aplikasi
Buka browser dan akses:
```
http://localhost/TUGASKRIPTO/E-VOTE/public/
```

## 🔐 Layanan Kriptografi

### 1. Otentikasi (bcrypt)
- Password user di-hash dengan bcrypt (cost factor: 12)
- Fungsi: `password_hash()` dan `password_verify()`

### 2. Integritas (SHA-256)
- Setiap vote di-hash untuk memastikan tidak ada modifikasi
- Fungsi: `hash('sha256', $data)`

### 3. Kerahasiaan (AES-256-CBC)
- Data vote dienkripsi sebelum disimpan
- Fungsi: `openssl_encrypt()` dan `openssl_decrypt()`

### 4. Non-Repudiation (HMAC-SHA256)
- Setiap user memiliki pasangan kunci unik
- Vote ditandatangani dengan private key
- Fungsi: `hash_hmac()` dan `random_bytes()`

## 📁 Struktur Folder

```
E-VOTE/
├── config/
│   └── database.php         # Konfigurasi database
├── crypto/
│   ├── DigitalSignature.php # HMAC-SHA256 signature
│   ├── Encryption.php       # AES-256-CBC encryption
│   └── Hashing.php          # SHA-256 hashing
├── includes/
│   ├── auth.php             # Authentication (bcrypt)
│   └── functions.php        # Helper functions
├── public/
│   ├── index.php            # Landing page
│   ├── register.php         # Registrasi user
│   ├── login.php            # Login user
│   ├── dashboard.php        # Dashboard user
│   ├── vote.php             # Halaman voting
│   ├── verify.php           # Verifikasi suara
│   ├── results.php          # Hasil pemilu
│   └── crypto_info.php      # Info kriptografi
└── sql/
    └── database.sql         # Schema database
```

## 👤 Default Admin
- Username: `admin`
- Password: `password`

## ⚠️ Catatan Keamanan

Aplikasi ini dibuat untuk **tujuan pembelajaran**. Untuk implementasi produksi, pertimbangkan:
- Gunakan HTTPS
- Implementasi rate limiting
- Audit logging yang lebih lengkap
- Penetration testing

## 📝 Lisensi

MIT License - Bebas digunakan untuk pembelajaran.

---

Dibuat untuk Tugas Kriptografi 🔒
