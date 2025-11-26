<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

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
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - E-Vote Pemilu</title>
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
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="vote.php">Voting</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="verify.php">Verifikasi</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="results.php">Hasil</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" 
                           data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user-circle me-1"></i><?= htmlspecialchars($user['nama']) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="profile.php">
                                <i class="fas fa-user me-2"></i>Profil</a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i>Keluar</a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <!-- Welcome Card -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h3 class="mb-2">
                            <i class="fas fa-hand-sparkles text-warning me-2"></i>
                            Selamat Datang, <?= htmlspecialchars($user['nama']) ?>!
                        </h3>
                        <p class="text-muted mb-0">
                            NIK: <?= htmlspecialchars($user['nik']) ?> | 
                            Email: <?= htmlspecialchars($user['email']) ?>
                        </p>
                    </div>
                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        <?php if ($user['has_voted']): ?>
                            <span class="badge bg-success fs-6 p-2">
                                <i class="fas fa-check-circle me-1"></i>Sudah Memilih
                            </span>
                        <?php else: ?>
                            <a href="vote.php" class="btn btn-danger btn-lg">
                                <i class="fas fa-vote-yea me-2"></i>Vote Sekarang!
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Status Voting -->
            <div class="col-md-6 mb-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-info-circle text-primary me-2"></i>Status Voting</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($user['has_voted']): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-check-circle text-success fa-5x mb-3"></i>
                                <h4 class="text-success">Anda Sudah Memilih</h4>
                                <p class="text-muted">
                                    Terima kasih telah berpartisipasi dalam pemilu. 
                                    Suara Anda telah terenkripsi dan ditandatangani.
                                </p>
                                <a href="verify.php" class="btn btn-outline-primary">
                                    <i class="fas fa-search me-2"></i>Verifikasi Suara
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-exclamation-circle text-warning fa-5x mb-3"></i>
                                <h4 class="text-warning">Belum Memilih</h4>
                                <p class="text-muted">
                                    Gunakan hak pilih Anda! Pilih kandidat yang menurut Anda terbaik.
                                </p>
                                <a href="vote.php" class="btn btn-danger">
                                    <i class="fas fa-vote-yea me-2"></i>Vote Sekarang
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="col-md-6 mb-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-chart-bar text-success me-2"></i>Statistik</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6 mb-3">
                                <h2 class="text-danger mb-0"><?= count($candidates) ?></h2>
                                <small class="text-muted">Paslon</small>
                            </div>
                            <div class="col-6 mb-3">
                                <h2 class="text-primary mb-0"><?= getTotalVotes() ?></h2>
                                <small class="text-muted">Suara Masuk</small>
                            </div>
                            <div class="col-6">
                                <h2 class="text-success mb-0"><?= getTotalVoters() ?></h2>
                                <small class="text-muted">Pemilih Terdaftar</small>
                            </div>
                            <div class="col-6">
                                <?php 
                                    $totalVoters = getTotalVoters();
                                    $totalVotes = getTotalVotes();
                                    $participation = $totalVoters > 0 ? round(($totalVotes / $totalVoters) * 100) : 0;
                                ?>
                                <h2 class="text-warning mb-0"><?= $participation ?>%</h2>
                                <small class="text-muted">Partisipasi</small>
                            </div>
                        </div>
                        <hr>
                        <a href="results.php" class="btn btn-outline-success w-100">
                            <i class="fas fa-chart-pie me-2"></i>Lihat Hasil Lengkap
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kandidat -->
        <h4 class="mb-3"><i class="fas fa-users text-danger me-2"></i>Pasangan Calon</h4>
        <div class="row">
            <?php foreach ($candidates as $candidate): 
                $images = $candidateImages[$candidate['nomor_urut']] ?? $candidateImages[1];
            ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm candidate-card" style="border-radius: 15px; overflow: hidden;">
                    <div class="card-header text-white text-center py-3" style="background: <?= $images['bg_color'] ?>;">
                        <div class="d-flex justify-content-center align-items-center gap-3">
                            <h1 class="display-4 mb-0 fw-bold"><?= $candidate['nomor_urut'] ?></h1>
                            <div class="d-flex align-items-end">
                                <img src="<?= $images['capres'] ?>" class="rounded-circle" 
                                     style="width: 60px; height: 60px; object-fit: cover; border: 3px solid white;">
                                <img src="<?= $images['cawapres'] ?>" class="rounded-circle" 
                                     style="width: 45px; height: 45px; object-fit: cover; border: 2px solid white; margin-left: -15px;">
                            </div>
                        </div>
                    </div>
                    <div class="card-body text-center">
                        <h5 class="card-title mb-0"><?= htmlspecialchars($candidate['nama_capres']) ?></h5>
                        <small class="text-muted">&amp;</small>
                        <h6 class="mb-2"><?= htmlspecialchars($candidate['nama_cawapres']) ?></h6>
                        <hr>
                        <p class="small text-muted mb-2">
                            <strong>Visi:</strong> <?= htmlspecialchars($candidate['visi']) ?>
                        </p>
                    </div>
                    <div class="card-footer bg-white border-0 text-center pb-3">
                        <span class="badge" style="background: <?= $images['bg_color'] ?>;">
                            <i class="fas fa-flag me-1"></i><?= htmlspecialchars($candidate['partai_pengusung']) ?>
                        </span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Menu Kriptografi -->
        <h4 class="mb-3 mt-4"><i class="fas fa-shield-alt text-primary me-2"></i>Menu Keamanan</h4>
        <div class="row">
            <div class="col-md-3 mb-3">
                <a href="crypto_info.php" class="text-decoration-none">
                    <div class="card border-0 shadow-sm h-100 hover-card">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-book text-primary fa-2x mb-3"></i>
                            <h6>Info Kriptografi</h6>
                            <small class="text-muted">Pelajari sistem keamanan</small>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-3 mb-3">
                <a href="verify.php" class="text-decoration-none">
                    <div class="card border-0 shadow-sm h-100 hover-card">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-search text-success fa-2x mb-3"></i>
                            <h6>Verifikasi Suara</h6>
                            <small class="text-muted">Cek integritas & signature</small>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-3 mb-3">
                <a href="audit_log.php" class="text-decoration-none">
                    <div class="card border-0 shadow-sm h-100 hover-card">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-history text-warning fa-2x mb-3"></i>
                            <h6>Audit Log</h6>
                            <small class="text-muted">Riwayat aktivitas</small>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-3 mb-3">
                <a href="my_keys.php" class="text-decoration-none">
                    <div class="card border-0 shadow-sm h-100 hover-card">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-key text-danger fa-2x mb-3"></i>
                            <h6>Kunci Saya</h6>
                            <small class="text-muted">Public key & info</small>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-3 mt-5">
        <div class="container text-center">
            <small class="text-muted">
                <i class="fas fa-shield-alt me-1"></i>
                E-Vote Pemilu - Dilindungi dengan bcrypt | SHA-256 | AES-256 | HMAC-SHA256
            </small>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
