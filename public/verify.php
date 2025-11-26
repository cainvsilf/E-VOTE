<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../crypto/Encryption.php';
require_once __DIR__ . '/../crypto/Hashing.php';
require_once __DIR__ . '/../crypto/DigitalSignature.php';

$auth = new Auth();
requireLogin();

$user = $auth->getCurrentUser();
$db = getDBConnection();

// Data gambar kandidat
$candidateImages = [
    1 => [
        'capres' => 'https://randomuser.me/api/portraits/men/32.jpg',
        'cawapres' => 'https://randomuser.me/api/portraits/women/44.jpg',
        'bg_color' => '#dc3545'
    ],
    2 => [
        'capres' => 'https://randomuser.me/api/portraits/men/52.jpg',
        'cawapres' => 'https://randomuser.me/api/portraits/women/68.jpg',
        'bg_color' => '#0d6efd'
    ],
    3 => [
        'capres' => 'https://randomuser.me/api/portraits/men/75.jpg',
        'cawapres' => 'https://randomuser.me/api/portraits/women/79.jpg',
        'bg_color' => '#198754'
    ]
];

$verificationResult = null;
$voteData = null;

// Ambil data vote user
$stmt = $db->prepare("SELECT * FROM votes WHERE user_id = ?");
$stmt->execute([$user['id']]);
$vote = $stmt->fetch();

// Ambil public key user
$stmt = $db->prepare("SELECT public_key FROM users WHERE id = ?");
$stmt->execute([$user['id']]);
$userData = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $vote) {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'verify_integrity') {
        // Verifikasi Integritas dengan SHA-256
        $encryption = new Encryption();
        $decrypted = $encryption->decryptVote($vote['encrypted_vote'], $vote['iv']);
        
        if ($decrypted) {
            $hashing = new Hashing();
            $calculatedHash = $hashing->hashVote(
                $decrypted['candidate_id'],
                $decrypted['user_id'],
                $decrypted['timestamp']
            );
            
            $verificationResult = [
                'type' => 'integrity',
                'status' => hash_equals($vote['vote_hash'], $calculatedHash),
                'stored_hash' => $vote['vote_hash'],
                'calculated_hash' => $calculatedHash
            ];
        }
    } elseif ($action === 'verify_signature') {
        // Verifikasi Digital Signature dengan HMAC-SHA256
        $encryption = new Encryption();
        $decrypted = $encryption->decryptVote($vote['encrypted_vote'], $vote['iv']);
        
        if ($decrypted && $userData['public_key']) {
            $digitalSignature = new DigitalSignature();
            $digitalSignature->setPublicKey($userData['public_key']);
            
            $isValid = $digitalSignature->verifyVoteSignature(
                $decrypted['candidate_id'],
                $decrypted['user_id'],
                $decrypted['timestamp'],
                $vote['vote_hash'],
                $vote['digital_signature']
            );
            
            $verificationResult = [
                'type' => 'signature',
                'status' => $isValid,
                'signature' => $vote['digital_signature']
            ];
        }
    } elseif ($action === 'decrypt_vote') {
        // Dekripsi vote untuk melihat isi
        $encryption = new Encryption();
        $decrypted = $encryption->decryptVote($vote['encrypted_vote'], $vote['iv']);
        
        if ($decrypted) {
            $candidate = getCandidateById($decrypted['candidate_id']);
            $voteData = [
                'candidate' => $candidate,
                'timestamp' => $decrypted['timestamp'],
                'raw' => $decrypted
            ];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Suara - E-Vote Pemilu</title>
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
        <div class="text-center mb-4">
            <h2><i class="fas fa-search text-success me-2"></i>Verifikasi Suara</h2>
            <p class="text-muted">Verifikasi integritas dan keaslian suara Anda</p>
        </div>

        <?php if (!$vote): ?>
        <div class="alert alert-warning text-center">
            <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
            <h5>Anda belum memberikan suara</h5>
            <p>Silakan vote terlebih dahulu untuk dapat melakukan verifikasi.</p>
            <a href="vote.php" class="btn btn-danger">
                <i class="fas fa-vote-yea me-2"></i>Vote Sekarang
            </a>
        </div>
        <?php else: ?>
        
        <div class="row">
            <!-- Info Suara -->
            <div class="col-lg-4 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Info Suara</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Waktu Vote:</strong><br>
                        <span class="text-muted"><?= formatDate($vote['timestamp']) ?></span></p>
                        
                        <p><strong>Vote Hash (SHA-256):</strong><br>
                        <code class="small text-break"><?= $vote['vote_hash'] ?></code></p>
                        
                        <p><strong>Encrypted Vote (AES-256):</strong><br>
                        <code class="small text-break"><?= substr($vote['encrypted_vote'], 0, 60) ?>...</code></p>
                        
                        <p><strong>Digital Signature (HMAC-SHA256):</strong><br>
                        <code class="small text-break"><?= substr($vote['digital_signature'], 0, 60) ?>...</code></p>
                    </div>
                </div>
            </div>

            <!-- Verification Actions -->
            <div class="col-lg-8">
                <!-- Hasil Verifikasi -->
                <?php if ($verificationResult): ?>
                <div class="card border-0 shadow-sm mb-4 <?= $verificationResult['status'] ? 'border-success' : 'border-danger' ?>" 
                     style="border-left: 5px solid !important;">
                    <div class="card-header <?= $verificationResult['status'] ? 'bg-success' : 'bg-danger' ?> text-white">
                        <h5 class="mb-0">
                            <?php if ($verificationResult['type'] === 'integrity'): ?>
                                <i class="fas fa-check-double me-2"></i>Hasil Verifikasi Integritas
                            <?php else: ?>
                                <i class="fas fa-signature me-2"></i>Hasil Verifikasi Signature
                            <?php endif; ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if ($verificationResult['status']): ?>
                            <div class="text-center py-3">
                                <i class="fas fa-check-circle text-success fa-4x mb-3"></i>
                                <h4 class="text-success">VALID!</h4>
                                <?php if ($verificationResult['type'] === 'integrity'): ?>
                                    <p>Data suara tidak dimodifikasi. Hash cocok!</p>
                                    <div class="text-start bg-light p-3 rounded">
                                        <small><strong>Stored Hash:</strong><br>
                                        <code><?= $verificationResult['stored_hash'] ?></code></small><br><br>
                                        <small><strong>Calculated Hash:</strong><br>
                                        <code><?= $verificationResult['calculated_hash'] ?></code></small>
                                    </div>
                                <?php else: ?>
                                    <p>Tanda tangan digital valid! Suara benar dari Anda.</p>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-3">
                                <i class="fas fa-times-circle text-danger fa-4x mb-3"></i>
                                <h4 class="text-danger">TIDAK VALID!</h4>
                                <?php if ($verificationResult['type'] === 'integrity'): ?>
                                    <p>PERINGATAN: Data suara mungkin telah dimodifikasi!</p>
                                <?php else: ?>
                                    <p>PERINGATAN: Tanda tangan digital tidak valid!</p>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($voteData): 
                    $images = $candidateImages[$voteData['candidate']['nomor_urut']] ?? $candidateImages[1];
                ?>
                <!-- Hasil Dekripsi -->
                <div class="card border-0 shadow-sm mb-4 border-warning" style="border-left: 5px solid !important;">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="fas fa-unlock me-2"></i>Data Vote (Dekripsi)</h5>
                    </div>
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-4 text-center">
                                <div class="position-relative d-inline-block">
                                    <img src="<?= $images['capres'] ?>" class="rounded-circle mb-2" 
                                         style="width: 100px; height: 100px; object-fit: cover; border: 4px solid <?= $images['bg_color'] ?>;">
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill text-white fs-5" 
                                          style="background: <?= $images['bg_color'] ?>;">
                                        <?= $voteData['candidate']['nomor_urut'] ?>
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <h4 style="color: <?= $images['bg_color'] ?>;"><?= htmlspecialchars($voteData['candidate']['nama_capres']) ?></h4>
                                <p class="text-muted mb-1">&amp; <?= htmlspecialchars($voteData['candidate']['nama_cawapres']) ?></p>
                                <span class="badge" style="background: <?= $images['bg_color'] ?>;">
                                    <i class="fas fa-flag me-1"></i><?= htmlspecialchars($voteData['candidate']['partai_pengusung']) ?>
                                </span>
                                <hr>
                                <small class="text-muted">
                                    <i class="fas fa-clock me-1"></i>Timestamp: <?= $voteData['timestamp'] ?>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Action Cards -->
                <div class="row">
                    <!-- Verifikasi Integritas -->
                    <div class="col-md-4 mb-3">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body text-center p-4">
                                <i class="fas fa-check-double fa-3x text-success mb-3"></i>
                                <h5>Verifikasi Integritas</h5>
                                <p class="text-muted small">
                                    Menggunakan <strong>SHA-256</strong> untuk memastikan data tidak diubah
                                </p>
                                <form method="POST">
                                    <input type="hidden" name="action" value="verify_integrity">
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-check me-1"></i>Verifikasi
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Verifikasi Signature -->
                    <div class="col-md-4 mb-3">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body text-center p-4">
                                <i class="fas fa-signature fa-3x text-danger mb-3"></i>
                                <h5>Verifikasi Signature</h5>
                                <p class="text-muted small">
                                    Menggunakan <strong>HMAC-SHA256</strong> untuk membuktikan keaslian pengirim
                                </p>
                                <form method="POST">
                                    <input type="hidden" name="action" value="verify_signature">
                                    <button type="submit" class="btn btn-danger">
                                        <i class="fas fa-pen me-1"></i>Verifikasi
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Dekripsi Vote -->
                    <div class="col-md-4 mb-3">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body text-center p-4">
                                <i class="fas fa-unlock fa-3x text-warning mb-3"></i>
                                <h5>Lihat Suara</h5>
                                <p class="text-muted small">
                                    Dekripsi suara dengan <strong>AES-256</strong> untuk melihat pilihan
                                </p>
                                <form method="POST">
                                    <input type="hidden" name="action" value="decrypt_vote">
                                    <button type="submit" class="btn btn-warning">
                                        <i class="fas fa-eye me-1"></i>Dekripsi
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Info Kriptografi -->
                <div class="card border-0 shadow-sm mt-3">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Tentang Verifikasi</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <h6 class="text-success"><i class="fas fa-check-double me-1"></i>Integritas</h6>
                                <small class="text-muted">
                                    Hash SHA-256 dihitung ulang dari data vote dan dibandingkan dengan hash tersimpan. 
                                    Jika cocok, data tidak dimodifikasi.
                                </small>
                            </div>
                            <div class="col-md-4">
                                <h6 class="text-danger"><i class="fas fa-signature me-1"></i>Non-Repudiation</h6>
                                <small class="text-muted">
                                    Signature diverifikasi dengan public key Anda. 
                                    Jika valid, membuktikan bahwa Anda yang membuat suara tersebut.
                                </small>
                            </div>
                            <div class="col-md-4">
                                <h6 class="text-warning"><i class="fas fa-lock me-1"></i>Kerahasiaan</h6>
                                <small class="text-muted">
                                    Data vote terenkripsi dengan AES-256. 
                                    Hanya sistem dengan key yang benar dapat mendekripsi.
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <footer class="bg-dark text-white py-3 mt-5">
        <div class="container text-center">
            <small class="text-muted">
                E-Vote Pemilu - Sistem Verifikasi Kriptografi
            </small>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
