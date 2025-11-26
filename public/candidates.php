<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

$auth = new Auth();
$isLoggedIn = $auth->isLoggedIn();
$user = $isLoggedIn ? $auth->getCurrentUser() : null;
$candidates = getCandidates();

// Data gambar kandidat (menggunakan placeholder profesional)
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
    <title>Kandidat - E-Vote Pemilu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .candidate-photo {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border: 4px solid white;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .candidate-photo-small {
            width: 90px;
            height: 90px;
            object-fit: cover;
            border: 3px solid white;
            box-shadow: 0 3px 10px rgba(0,0,0,0.15);
        }
        .candidate-card:hover {
            transform: translateY(-10px);
        }
        .paslon-header {
            position: relative;
            padding: 30px 20px 60px;
        }
        .paslon-photos {
            position: absolute;
            bottom: -50px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: -20px;
        }
        .nomor-urut-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            width: 50px;
            height: 50px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: bold;
            box-shadow: 0 3px 10px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body class="bg-light">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-danger">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-vote-yea me-2"></i>E-Vote Pemilu
            </a>
            <div class="navbar-nav ms-auto">
                <?php if ($isLoggedIn): ?>
                <a class="nav-link" href="dashboard.php">
                    <i class="fas fa-arrow-left me-1"></i>Dashboard
                </a>
                <?php else: ?>
                <a class="nav-link" href="index.php">
                    <i class="fas fa-home me-1"></i>Beranda
                </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="py-4 text-white text-center" style="background: linear-gradient(135deg, #dc3545, #c82333);">
        <div class="container">
            <h2 class="mb-2"><i class="fas fa-users me-2"></i>Pasangan Calon Presiden & Wakil Presiden</h2>
            <p class="mb-0 opacity-75">Pemilihan Umum Republik Indonesia 2024</p>
        </div>
    </section>

    <div class="container py-5">
        <div class="row justify-content-center">
            <?php foreach ($candidates as $candidate): 
                $images = $candidateImages[$candidate['nomor_urut']] ?? $candidateImages[1];
            ?>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card h-100 border-0 shadow-lg candidate-card" style="border-radius: 20px; overflow: hidden;">
                    <!-- Header dengan foto -->
                    <div class="paslon-header text-white text-center" style="background: <?= $images['bg_color'] ?>;">
                        <span class="nomor-urut-badge" style="color: <?= $images['bg_color'] ?>;">
                            <?= $candidate['nomor_urut'] ?>
                        </span>
                        <h5 class="mb-0 opacity-75">PASLON</h5>
                        <div class="paslon-photos">
                            <img src="<?= $images['capres'] ?>" 
                                 class="rounded-circle candidate-photo" 
                                 alt="<?= htmlspecialchars($candidate['nama_capres']) ?>"
                                 style="position: relative; z-index: 2;">
                            <img src="<?= $images['cawapres'] ?>" 
                                 class="rounded-circle candidate-photo-small" 
                                 alt="<?= htmlspecialchars($candidate['nama_cawapres']) ?>"
                                 style="position: relative; left: -20px; z-index: 1; margin-top: 30px;">
                        </div>
                    </div>
                    
                    <div class="card-body pt-5 mt-4">
                        <!-- Nama Paslon -->
                        <div class="text-center mb-4">
                            <h4 class="mb-1" style="color: <?= $images['bg_color'] ?>;">
                                <?= htmlspecialchars($candidate['nama_capres']) ?>
                            </h4>
                            <small class="text-muted">Calon Presiden</small>
                            <div class="my-2">
                                <span class="text-muted">&amp;</span>
                            </div>
                            <h5 class="mb-1"><?= htmlspecialchars($candidate['nama_cawapres']) ?></h5>
                            <small class="text-muted">Calon Wakil Presiden</small>
                        </div>
                        
                        <hr>
                        
                        <!-- Partai -->
                        <div class="text-center mb-3">
                            <span class="badge rounded-pill px-3 py-2" style="background: <?= $images['bg_color'] ?>;">
                                <i class="fas fa-flag me-1"></i><?= htmlspecialchars($candidate['partai_pengusung']) ?>
                            </span>
                        </div>
                        
                        <!-- Visi Misi -->
                        <div class="mb-3">
                            <h6 class="fw-bold"><i class="fas fa-bullseye text-danger me-2"></i>Visi</h6>
                            <p class="small text-muted mb-0"><?= htmlspecialchars($candidate['visi']) ?></p>
                        </div>
                        
                        <div class="mb-3">
                            <h6 class="fw-bold"><i class="fas fa-tasks text-success me-2"></i>Misi</h6>
                            <p class="small text-muted mb-0"><?= htmlspecialchars($candidate['misi']) ?></p>
                        </div>
                    </div>
                    
                    <div class="card-footer bg-white border-0 text-center pb-4">
                        <?php if ($isLoggedIn && !$user['has_voted']): ?>
                        <a href="vote.php" class="btn btn-outline-danger">
                            <i class="fas fa-vote-yea me-1"></i>Pilih Paslon <?= $candidate['nomor_urut'] ?>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Call to Action -->
        <div class="text-center mt-4">
            <?php if (!$isLoggedIn): ?>
            <div class="card border-0 shadow-sm p-4" style="background: linear-gradient(135deg, #fff5f5, #fff);">
                <h4 class="text-danger mb-3">Siap Memberikan Suara?</h4>
                <p class="text-muted">Daftar atau masuk untuk memberikan suara Anda</p>
                <div class="d-flex gap-3 justify-content-center">
                    <a href="register.php" class="btn btn-danger btn-lg">
                        <i class="fas fa-user-plus me-2"></i>Daftar Sekarang
                    </a>
                    <a href="login.php" class="btn btn-outline-danger btn-lg">
                        <i class="fas fa-sign-in-alt me-2"></i>Masuk
                    </a>
                </div>
            </div>
            <?php elseif ($user['has_voted']): ?>
            <div class="alert alert-success py-3">
                <i class="fas fa-check-circle fa-2x mb-2"></i>
                <h5>Anda Sudah Memberikan Suara</h5>
                <p class="mb-2">Terima kasih telah berpartisipasi dalam Pemilu!</p>
                <a href="verify.php" class="btn btn-success">
                    <i class="fas fa-search me-1"></i>Verifikasi Suara
                </a>
            </div>
            <?php else: ?>
            <a href="vote.php" class="btn btn-danger btn-lg px-5">
                <i class="fas fa-vote-yea me-2"></i>Berikan Suara Anda Sekarang
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Info Section -->
    <section class="py-4 bg-white">
        <div class="container">
            <div class="row text-center">
                <div class="col-md-4 mb-3">
                    <i class="fas fa-shield-alt fa-2x text-success mb-2"></i>
                    <h6>Suara Terenkripsi</h6>
                    <small class="text-muted">Menggunakan AES-256</small>
                </div>
                <div class="col-md-4 mb-3">
                    <i class="fas fa-check-double fa-2x text-primary mb-2"></i>
                    <h6>Integritas Terjamin</h6>
                    <small class="text-muted">Hash SHA-256</small>
                </div>
                <div class="col-md-4 mb-3">
                    <i class="fas fa-signature fa-2x text-danger mb-2"></i>
                    <h6>Tanda Tangan Digital</h6>
                    <small class="text-muted">HMAC-SHA256</small>
                </div>
            </div>
        </div>
    </section>

    <footer class="bg-dark text-white py-3">
        <div class="container text-center">
            <small class="text-muted">
                <i class="fas fa-vote-yea me-1"></i>E-Vote Pemilu - Sistem Pemilihan Elektronik yang Aman
            </small>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
