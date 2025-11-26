<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../crypto/DigitalSignature.php';

$auth = new Auth();
requireLogin();

$user = $auth->getCurrentUser();
$db = getDBConnection();

$success = '';
$error = '';

// Ambil data lengkap user
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user['id']]);
$userData = $stmt->fetch();

// Ambil log aktivitas
$stmt = $db->prepare("SELECT * FROM vote_logs WHERE user_id = ? ORDER BY timestamp DESC LIMIT 10");
$stmt->execute([$user['id']]);
$logs = $stmt->fetchAll();

// Proses update password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'change_password') {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $error = 'Semua field harus diisi';
        } elseif (!password_verify($currentPassword, $userData['password'])) {
            $error = 'Password saat ini salah';
        } elseif ($newPassword !== $confirmPassword) {
            $error = 'Konfirmasi password tidak cocok';
        } elseif (strlen($newPassword) < 8) {
            $error = 'Password baru minimal 8 karakter';
        } else {
            $newHash = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);
            $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$newHash, $user['id']]);
            
            $auth->logAction($user['id'], 'PASSWORD_CHANGED', 'Password berhasil diubah');
            $success = 'Password berhasil diubah!';
        }
    }
}

// Get key fingerprint
$digitalSignature = new DigitalSignature();
$keyFingerprint = '';
if ($userData['public_key']) {
    $keyFingerprint = $digitalSignature->getKeyFingerprint($userData['public_key']);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil - E-Vote Pemilu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-danger">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-vote-yea me-2"></i>E-Vote Pemilu
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="dashboard.php">
                    <i class="fas fa-arrow-left me-1"></i>Dashboard
                </a>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <div class="row">
            <!-- Profile Card -->
            <div class="col-lg-4 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-danger text-white text-center py-4">
                        <div class="mb-3">
                            <i class="fas fa-user-circle fa-5x"></i>
                        </div>
                        <h4 class="mb-0"><?= htmlspecialchars($userData['nama_lengkap']) ?></h4>
                        <small class="opacity-75">Pemilih Terdaftar</small>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-3">
                                <small class="text-muted d-block">NIK</small>
                                <strong><i class="fas fa-id-card me-2 text-danger"></i><?= htmlspecialchars($userData['nik']) ?></strong>
                            </li>
                            <li class="mb-3">
                                <small class="text-muted d-block">Email</small>
                                <strong><i class="fas fa-envelope me-2 text-danger"></i><?= htmlspecialchars($userData['email']) ?></strong>
                            </li>
                            <li class="mb-3">
                                <small class="text-muted d-block">Status Voting</small>
                                <?php if ($userData['has_voted']): ?>
                                    <span class="badge bg-success"><i class="fas fa-check me-1"></i>Sudah Voting</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i>Belum Voting</span>
                                <?php endif; ?>
                            </li>
                            <li class="mb-3">
                                <small class="text-muted d-block">Terdaftar Sejak</small>
                                <strong><i class="fas fa-calendar me-2 text-danger"></i><?= formatDate($userData['created_at']) ?></strong>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Key Fingerprint -->
                <div class="card border-0 shadow-sm mt-3">
                    <div class="card-header bg-warning text-dark">
                        <h6 class="mb-0"><i class="fas fa-fingerprint me-2"></i>Key Fingerprint</h6>
                    </div>
                    <div class="card-body">
                        <p class="small text-muted mb-2">
                            Fingerprint dari public key Anda (HMAC-SHA256):
                        </p>
                        <code class="d-block text-break small"><?= htmlspecialchars($keyFingerprint) ?></code>
                        <hr>
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            Fingerprint ini unik untuk setiap pengguna dan digunakan untuk verifikasi identitas digital.
                        </small>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-lg-8">
                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fas fa-check-circle me-2"></i><?= $success ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-circle me-2"></i><?= $error ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Ubah Password -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-key me-2"></i>Ubah Password</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="change_password">
                            
                            <div class="mb-3">
                                <label for="current_password" class="form-label">Password Saat Ini</label>
                                <input type="password" class="form-control" id="current_password" 
                                       name="current_password" required>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="new_password" class="form-label">Password Baru</label>
                                    <input type="password" class="form-control" id="new_password" 
                                           name="new_password" minlength="8" required>
                                    <small class="text-muted">Minimal 8 karakter</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="confirm_password" class="form-label">Konfirmasi Password Baru</label>
                                    <input type="password" class="form-control" id="confirm_password" 
                                           name="confirm_password" required>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Simpan Password Baru
                            </button>
                        </form>
                        
                        <div class="mt-3 p-3 bg-light rounded">
                            <small class="text-muted">
                                <i class="fas fa-shield-alt me-1 text-success"></i>
                                Password akan di-hash dengan <strong>bcrypt</strong> (cost factor: 12) sebelum disimpan.
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Info Kriptografi Akun -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Keamanan Akun</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="p-3 bg-light rounded h-100">
                                    <h6><i class="fas fa-lock text-primary me-2"></i>Password (bcrypt)</h6>
                                    <small class="text-muted">
                                        Password Anda dilindungi dengan algoritma bcrypt dengan cost factor 12.
                                        Ini memastikan password tidak dapat dibaca meski database diretas.
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="p-3 bg-light rounded h-100">
                                    <h6><i class="fas fa-signature text-danger me-2"></i>Digital Signature (HMAC-SHA256)</h6>
                                    <small class="text-muted">
                                        Anda memiliki pasangan kunci unik untuk menandatangani suara.
                                        Private key hanya Anda yang punya, public key tersimpan di sistem.
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Log Aktivitas -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-history me-2"></i>Log Aktivitas</h5>
                        <span class="badge bg-light text-dark"><?= count($logs) ?> aktivitas terakhir</span>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($logs)): ?>
                            <div class="text-center py-4 text-muted">
                                <i class="fas fa-inbox fa-2x mb-2"></i>
                                <p class="mb-0">Belum ada aktivitas tercatat</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Waktu</th>
                                            <th>Aksi</th>
                                            <th>Detail</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($logs as $log): ?>
                                        <tr>
                                            <td>
                                                <small><?= formatDate($log['timestamp']) ?></small>
                                            </td>
                                            <td>
                                                <?php
                                                $actionBadge = 'secondary';
                                                $actionIcon = 'info-circle';
                                                switch ($log['action']) {
                                                    case 'LOGIN':
                                                        $actionBadge = 'primary';
                                                        $actionIcon = 'sign-in-alt';
                                                        break;
                                                    case 'LOGOUT':
                                                        $actionBadge = 'secondary';
                                                        $actionIcon = 'sign-out-alt';
                                                        break;
                                                    case 'VOTE_SUBMITTED':
                                                        $actionBadge = 'success';
                                                        $actionIcon = 'vote-yea';
                                                        break;
                                                    case 'PASSWORD_CHANGED':
                                                        $actionBadge = 'warning';
                                                        $actionIcon = 'key';
                                                        break;
                                                    case 'REGISTER':
                                                        $actionBadge = 'info';
                                                        $actionIcon = 'user-plus';
                                                        break;
                                                }
                                                ?>
                                                <span class="badge bg-<?= $actionBadge ?>">
                                                    <i class="fas fa-<?= $actionIcon ?> me-1"></i>
                                                    <?= htmlspecialchars($log['action']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    IP: <?= htmlspecialchars($log['ip_address'] ?? '-') ?>
                                                </small>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-dark text-white py-3 mt-5">
        <div class="container text-center">
            <small class="text-muted">
                E-Vote Pemilu - Dilindungi dengan bcrypt | SHA-256 | AES-256 | HMAC-SHA256
            </small>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
