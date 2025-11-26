<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../crypto/Encryption.php';
require_once __DIR__ . '/../crypto/Hashing.php';
require_once __DIR__ . '/../crypto/DigitalSignature.php';

$auth = new Auth();
requireLogin();

$user = $auth->getCurrentUser();
$candidates = getCandidates();

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

$error = '';
$success = '';
$voteDetails = null;

// Cek apakah sudah voting
if ($user['has_voted']) {
    header('Location: already_voted.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $candidateId = intval($_POST['candidate_id'] ?? 0);
    $privateKeyPem = trim($_POST['private_key'] ?? '');
    
    // Validasi
    if ($candidateId <= 0) {
        $error = 'Pilih salah satu kandidat';
    } elseif (empty($privateKeyPem)) {
        $error = 'Private key harus diisi untuk menandatangani suara';
    } else {
        try {
            $db = getDBConnection();
            $timestamp = date('Y-m-d H:i:s');
            
            // 1. INTEGRITAS - Hash vote dengan SHA-256
            $hashing = new Hashing();
            $voteHash = $hashing->hashVote($candidateId, $user['id'], $timestamp);
            
            // 2. KERAHASIAAN - Enkripsi vote dengan AES-256
            $encryption = new Encryption();
            $encryptedData = $encryption->encryptVote($candidateId, $user['id'], $timestamp);
            
            // 3. NON-REPUDIATION - Tanda tangan digital dengan HMAC-SHA256
            $digitalSignature = new DigitalSignature();
            $digitalSignature->setPrivateKey($privateKeyPem);
            $signature = $digitalSignature->signVote($candidateId, $user['id'], $timestamp, $voteHash);
            
            // Simpan ke database
            $stmt = $db->prepare(
                "INSERT INTO votes (user_id, encrypted_vote, vote_hash, digital_signature, iv, timestamp) 
                 VALUES (?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([
                $user['id'],
                $encryptedData['ciphertext'],
                $voteHash,
                $signature,
                $encryptedData['iv'],
                $timestamp
            ]);
            
            // Update status voting user
            $auth->updateVotingStatus($user['id']);
            
            // Log action
            $auth->logAction($user['id'], 'VOTE_SUBMITTED', 'Vote encrypted and signed');
            
            // Simpan detail untuk ditampilkan
            $voteDetails = [
                'candidate_id' => $candidateId,
                'timestamp' => $timestamp,
                'hash' => $voteHash,
                'signature' => substr($signature, 0, 50) . '...',
                'encrypted' => substr($encryptedData['ciphertext'], 0, 50) . '...'
            ];
            
            $success = 'Suara Anda berhasil dicatat!';
            
        } catch (Exception $e) {
            $error = 'Error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voting - E-Vote Pemilu</title>
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
        <?php if ($voteDetails): ?>
        <!-- Success - Vote Recorded -->
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow-lg">
                    <div class="card-header bg-success text-white text-center py-4">
                        <i class="fas fa-check-circle fa-4x mb-3"></i>
                        <h3>Suara Anda Berhasil Dicatat!</h3>
                    </div>
                    <div class="card-body p-4">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Suara Anda telah diproses dengan 3 layanan kriptografi untuk menjamin keamanan.
                        </div>
                        
                        <h5 class="mb-3">Detail Kriptografi:</h5>
                        
                        <div class="card mb-3 border-success">
                            <div class="card-header bg-success text-white">
                                <i class="fas fa-check-double me-2"></i>1. Integritas (SHA-256)
                            </div>
                            <div class="card-body">
                                <code class="d-block text-break"><?= htmlspecialchars($voteDetails['hash']) ?></code>
                                <small class="text-muted">Hash ini memastikan suara tidak dapat dimodifikasi</small>
                            </div>
                        </div>
                        
                        <div class="card mb-3 border-warning">
                            <div class="card-header bg-warning text-dark">
                                <i class="fas fa-lock me-2"></i>2. Kerahasiaan (AES-256-CBC)
                            </div>
                            <div class="card-body">
                                <code class="d-block text-break"><?= htmlspecialchars($voteDetails['encrypted']) ?></code>
                                <small class="text-muted">Data suara terenkripsi dan tidak dapat dibaca</small>
                            </div>
                        </div>
                        
                        <div class="card mb-3 border-danger">
                            <div class="card-header bg-danger text-white">
                                <i class="fas fa-signature me-2"></i>3. Non-Repudiation (HMAC-SHA256 Signature)
                            </div>
                            <div class="card-body">
                                <code class="d-block text-break"><?= htmlspecialchars($voteDetails['signature']) ?></code>
                                <small class="text-muted">Tanda tangan digital membuktikan Anda yang memberikan suara</small>
                            </div>
                        </div>
                        
                        <div class="text-center mt-4">
                            <a href="dashboard.php" class="btn btn-primary btn-lg me-2">
                                <i class="fas fa-home me-2"></i>Kembali ke Dashboard
                            </a>
                            <a href="verify.php" class="btn btn-outline-success btn-lg">
                                <i class="fas fa-search me-2"></i>Verifikasi Suara
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <?php else: ?>
        <!-- Voting Form -->
        <div class="text-center mb-4">
            <h2><i class="fas fa-vote-yea text-danger me-2"></i>Pemilihan Umum</h2>
            <p class="text-muted">Pilih satu pasangan calon dan tandatangani suara Anda</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i><?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" id="voteForm">
            <!-- Kandidat Selection -->
            <div class="row mb-4">
                <?php foreach ($candidates as $candidate): 
                    $images = $candidateImages[$candidate['nomor_urut']] ?? $candidateImages[1];
                ?>
                <div class="col-md-4 mb-3">
                    <div class="card h-100 border-3 candidate-select-card" 
                         onclick="selectCandidate(<?= $candidate['id'] ?>)" style="border-radius: 15px; overflow: hidden;">
                        <input type="radio" name="candidate_id" value="<?= $candidate['id'] ?>" 
                               id="candidate_<?= $candidate['id'] ?>" class="d-none" required>
                        <div class="card-header text-white text-center py-4" style="background: <?= $images['bg_color'] ?>;">
                            <h1 class="display-3 mb-2 fw-bold"><?= $candidate['nomor_urut'] ?></h1>
                            <div class="d-flex justify-content-center align-items-end gap-2">
                                <img src="<?= $images['capres'] ?>" class="rounded-circle" 
                                     style="width: 70px; height: 70px; object-fit: cover; border: 3px solid white;">
                                <img src="<?= $images['cawapres'] ?>" class="rounded-circle" 
                                     style="width: 55px; height: 55px; object-fit: cover; border: 2px solid white;">
                            </div>
                        </div>
                        <div class="card-body text-center">
                            <h5 class="mb-0"><?= htmlspecialchars($candidate['nama_capres']) ?></h5>
                            <small class="text-muted">&amp;</small>
                            <h6 class="mb-2"><?= htmlspecialchars($candidate['nama_cawapres']) ?></h6>
                            <span class="badge bg-secondary">
                                <i class="fas fa-flag me-1"></i>
                                <?= htmlspecialchars($candidate['partai_pengusung']) ?>
                            </span>
                        </div>
                        <div class="card-footer text-center bg-white py-3">
                            <span class="select-indicator">
                                <i class="far fa-circle fa-2x text-muted"></i>
                            </span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Private Key Input -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-key me-2"></i>Tanda Tangan Digital (Non-Repudiation)
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Masukkan private key yang Anda dapatkan saat registrasi untuk menandatangani suara Anda.
                        Private key digunakan untuk membuktikan bahwa ANDA yang memberikan suara ini.
                    </div>
                    
                    <div class="mb-3">
                        <label for="private_key" class="form-label">Private Key (HMAC-SHA256):</label>
                        <textarea class="form-control font-monospace" id="private_key" name="private_key" 
                                  rows="8" required placeholder="-----BEGIN EVOTE PRIVATE KEY-----
...
-----END EVOTE PRIVATE KEY-----"></textarea>
                        <small class="text-muted">
                            Paste private key Anda di sini. File biasanya bernama <code>private_key_evote.pem</code>
                        </small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Atau upload file:</label>
                        <input type="file" class="form-control" id="keyFile" accept=".pem,.txt" 
                               onchange="loadPrivateKey(this)">
                    </div>
                </div>
            </div>

            <!-- Crypto Info -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Proses Keamanan</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="text-center p-3">
                                <i class="fas fa-hashtag fa-2x text-success mb-2"></i>
                                <h6>1. Integritas</h6>
                                <small class="text-muted">Suara di-hash dengan SHA-256</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3">
                                <i class="fas fa-lock fa-2x text-warning mb-2"></i>
                                <h6>2. Kerahasiaan</h6>
                                <small class="text-muted">Suara dienkripsi dengan AES-256</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3">
                                <i class="fas fa-signature fa-2x text-danger mb-2"></i>
                                <h6>3. Non-Repudiation</h6>
                                <small class="text-muted">Suara ditandatangani dengan HMAC</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="text-center">
                <button type="submit" class="btn btn-danger btn-lg px-5" id="submitBtn" disabled>
                    <i class="fas fa-vote-yea me-2"></i>Kirim Suara
                </button>
                <p class="text-muted mt-2">
                    <small><i class="fas fa-exclamation-triangle me-1"></i>
                    Pastikan pilihan Anda sudah benar. Suara tidak dapat diubah.</small>
                </p>
            </div>
        </form>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let selectedCandidate = null;
        
        function selectCandidate(id) {
            // Remove previous selection
            document.querySelectorAll('.candidate-select-card').forEach(card => {
                card.classList.remove('border-success', 'selected');
                card.querySelector('.select-indicator').innerHTML = '<i class="far fa-circle fa-2x text-muted"></i>';
            });
            
            // Select new candidate
            const radio = document.getElementById('candidate_' + id);
            radio.checked = true;
            
            const card = radio.closest('.candidate-select-card');
            card.classList.add('border-success', 'selected');
            card.querySelector('.select-indicator').innerHTML = '<i class="fas fa-check-circle fa-2x text-success"></i>';
            
            selectedCandidate = id;
            updateSubmitButton();
        }
        
        function loadPrivateKey(input) {
            const file = input.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('private_key').value = e.target.result;
                    updateSubmitButton();
                };
                reader.readAsText(file);
            }
        }
        
        function updateSubmitButton() {
            const privateKey = document.getElementById('private_key').value.trim();
            const submitBtn = document.getElementById('submitBtn');
            
            if (selectedCandidate && (privateKey.includes('-----BEGIN EVOTE') || privateKey.includes('-----BEGIN PRIVATE'))) {
                submitBtn.disabled = false;
            } else {
                submitBtn.disabled = true;
            }
        }
        
        document.getElementById('private_key').addEventListener('input', updateSubmitButton);
        
        document.getElementById('voteForm').addEventListener('submit', function(e) {
            if (!confirm('Apakah Anda yakin dengan pilihan Anda? Suara tidak dapat diubah setelah dikirim.')) {
                e.preventDefault();
            }
        });
    </script>

    <style>
        .candidate-select-card {
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .candidate-select-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .candidate-select-card.selected {
            border-color: #198754 !important;
            box-shadow: 0 0 20px rgba(25, 135, 84, 0.3);
        }
    </style>
</body>
</html>
