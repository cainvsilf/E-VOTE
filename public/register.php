<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

$auth = new Auth();

// Redirect jika sudah login
if ($auth->isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
$success = '';
$privateKey = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nik = sanitize($_POST['nik'] ?? '');
    $namaLengkap = sanitize($_POST['nama_lengkap'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Validasi
    if (empty($nik) || empty($namaLengkap) || empty($email) || empty($password)) {
        $error = 'Semua field harus diisi';
    } elseif ($password !== $confirmPassword) {
        $error = 'Konfirmasi password tidak cocok';
    } elseif (strlen($password) < 8) {
        $error = 'Password minimal 8 karakter';
    } else {
        $result = $auth->register($nik, $namaLengkap, $email, $password);
        if ($result['success']) {
            $success = $result['message'];
            $privateKey = $result['private_key'];
        } else {
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi - E-Vote Pemilu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="text-center mb-4">
                    <a href="index.php" class="text-decoration-none">
                        <h2 class="text-danger">
                            <i class="fas fa-vote-yea me-2"></i>E-Vote Pemilu
                        </h2>
                    </a>
                </div>

                <?php if ($privateKey): ?>
                <!-- Modal Private Key -->
                <div class="card shadow-lg border-0 border-start border-5 border-warning">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0">
                            <i class="fas fa-key me-2"></i>SIMPAN PRIVATE KEY ANDA!
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>PENTING!</strong> Simpan private key ini dengan aman. 
                            Private key digunakan untuk menandatangani suara Anda (Digital Signature).
                            <br><strong>Private key TIDAK akan ditampilkan lagi!</strong>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Private Key (HMAC-SHA256):</label>
                            <textarea class="form-control font-monospace" rows="10" readonly id="privateKeyText"><?= htmlspecialchars($privateKey) ?></textarea>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button class="btn btn-warning" onclick="downloadPrivateKey()">
                                <i class="fas fa-download me-2"></i>Download Private Key
                            </button>
                            <button class="btn btn-outline-secondary" onclick="copyPrivateKey()">
                                <i class="fas fa-copy me-2"></i>Copy to Clipboard
                            </button>
                            <a href="login.php" class="btn btn-success">
                                <i class="fas fa-sign-in-alt me-2"></i>Lanjut ke Login
                            </a>
                        </div>
                        
                        <div class="mt-3 p-3 bg-light rounded">
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                <strong>Info Kriptografi:</strong><br>
                                - Private key digunakan untuk <strong>Digital Signature (Non-Repudiation)</strong><br>
                                - Password disimpan dengan <strong>bcrypt hash (Authentication)</strong><br>
                                - Public key disimpan di database untuk verifikasi signature
                            </small>
                        </div>
                    </div>
                </div>
                
                <?php else: ?>
                <!-- Form Registrasi -->
                <div class="card shadow-lg border-0">
                    <div class="card-header bg-danger text-white text-center py-3">
                        <h4 class="mb-0"><i class="fas fa-user-plus me-2"></i>Registrasi Pemilih</h4>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle me-2"></i><?= $error ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="nik" class="form-label">
                                    <i class="fas fa-id-card me-1"></i>NIK (16 digit)
                                </label>
                                <input type="text" class="form-control form-control-lg" id="nik" name="nik" 
                                       placeholder="Masukkan 16 digit NIK" maxlength="16" pattern="\d{16}"
                                       required value="<?= htmlspecialchars($_POST['nik'] ?? '') ?>">
                                <small class="text-muted">Nomor Induk Kependudukan dari KTP</small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="nama_lengkap" class="form-label">
                                    <i class="fas fa-user me-1"></i>Nama Lengkap
                                </label>
                                <input type="text" class="form-control form-control-lg" id="nama_lengkap" 
                                       name="nama_lengkap" placeholder="Nama sesuai KTP" required
                                       value="<?= htmlspecialchars($_POST['nama_lengkap'] ?? '') ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope me-1"></i>Email
                                </label>
                                <input type="email" class="form-control form-control-lg" id="email" name="email" 
                                       placeholder="contoh@email.com" required
                                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">
                                    <i class="fas fa-lock me-1"></i>Password
                                </label>
                                <input type="password" class="form-control form-control-lg" id="password" 
                                       name="password" placeholder="Minimal 8 karakter" minlength="8" required>
                                <small class="text-muted">Password akan di-hash dengan bcrypt</small>
                            </div>
                            
                            <div class="mb-4">
                                <label for="confirm_password" class="form-label">
                                    <i class="fas fa-lock me-1"></i>Konfirmasi Password
                                </label>
                                <input type="password" class="form-control form-control-lg" id="confirm_password" 
                                       name="confirm_password" placeholder="Ulangi password" required>
                            </div>
                            
                            <button type="submit" class="btn btn-danger btn-lg w-100 mb-3">
                                <i class="fas fa-user-plus me-2"></i>Daftar
                            </button>
                        </form>
                        
                        <div class="text-center">
                            <p class="mb-0">Sudah punya akun? 
                                <a href="login.php" class="text-danger fw-bold">Masuk</a>
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Info Keamanan -->
                <div class="card mt-3 border-0 bg-white shadow-sm">
                    <div class="card-body">
                        <h6><i class="fas fa-shield-alt text-success me-2"></i>Proses Keamanan Registrasi:</h6>
                        <ul class="mb-0 small text-muted">
                            <li>Password di-hash dengan <code>bcrypt</code> sebelum disimpan</li>
                            <li>Sistem akan generate pasangan kunci <code>HMAC-SHA256</code> untuk Anda</li>
                            <li>Private key untuk menandatangani suara (simpan dengan aman!)</li>
                            <li>Public key disimpan di database untuk verifikasi</li>
                        </ul>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="text-center mt-3">
                    <a href="index.php" class="text-muted">
                        <i class="fas fa-arrow-left me-1"></i>Kembali ke Beranda
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function downloadPrivateKey() {
            const privateKey = document.getElementById('privateKeyText').value;
            const blob = new Blob([privateKey], { type: 'text/plain' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'private_key_evote.pem';
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
            alert('Private key berhasil didownload! Simpan file ini dengan aman.');
        }
        
        function copyPrivateKey() {
            const privateKey = document.getElementById('privateKeyText');
            privateKey.select();
            document.execCommand('copy');
            alert('Private key berhasil dicopy ke clipboard!');
        }
    </script>
</body>
</html>
