<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

$auth = new Auth();
$isLoggedIn = $auth->isLoggedIn();
$user = $isLoggedIn ? $auth->getCurrentUser() : null;

$results = getVoteResults();
$totalVotes = getTotalVotes();
$totalVoters = getTotalVoters();

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

// Hitung persentase
$maxVotes = 0;
foreach ($results as $r) {
    if ($r['count'] > $maxVotes) $maxVotes = $r['count'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Pemilu - E-Vote Pemilu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                    <a class="nav-link" href="login.php">
                        <i class="fas fa-sign-in-alt me-1"></i>Login
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <div class="text-center mb-5">
            <h2><i class="fas fa-chart-pie text-danger me-2"></i>Hasil Pemilihan Umum</h2>
            <p class="text-muted">Real Count - Data terenkripsi dan terverifikasi</p>
        </div>

        <!-- Statistik Umum -->
        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <div class="card border-0 shadow-sm text-center py-4">
                    <i class="fas fa-users fa-2x text-primary mb-2"></i>
                    <h2 class="mb-0"><?= $totalVoters ?></h2>
                    <small class="text-muted">Pemilih Terdaftar</small>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card border-0 shadow-sm text-center py-4">
                    <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                    <h2 class="mb-0"><?= $totalVotes ?></h2>
                    <small class="text-muted">Suara Masuk</small>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card border-0 shadow-sm text-center py-4">
                    <i class="fas fa-percentage fa-2x text-warning mb-2"></i>
                    <h2 class="mb-0"><?= $totalVoters > 0 ? round(($totalVotes / $totalVoters) * 100, 1) : 0 ?>%</h2>
                    <small class="text-muted">Partisipasi</small>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Chart -->
            <div class="col-lg-5 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Diagram Perolehan Suara</h5>
                    </div>
                    <div class="card-body d-flex align-items-center justify-content-center">
                        <canvas id="voteChart" style="max-height: 300px;"></canvas>
                    </div>
                </div>
            </div>

            <!-- Results List -->
            <div class="col-lg-7 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-list-ol me-2"></i>Perolehan Suara</h5>
                    </div>
                    <div class="card-body">
                        <?php 
                        $rank = 1;
                        // Sort by count descending
                        usort($results, function($a, $b) {
                            return $b['count'] - $a['count'];
                        });
                        
                        foreach ($results as $result): 
                            $percentage = $totalVotes > 0 ? round(($result['count'] / $totalVotes) * 100, 1) : 0;
                            $isWinning = $result['count'] === $maxVotes && $maxVotes > 0;
                            $images = $candidateImages[$result['candidate']['nomor_urut']] ?? $candidateImages[1];
                        ?>
                        <div class="card mb-3 <?= $isWinning ? 'border-success border-2' : '' ?>" style="border-radius: 12px; overflow: hidden;">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <div class="position-relative">
                                            <img src="<?= $images['capres'] ?>" class="rounded-circle" 
                                                 style="width: 60px; height: 60px; object-fit: cover; border: 3px solid <?= $images['bg_color'] ?>;">
                                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-circle text-white" 
                                                  style="background: <?= $images['bg_color'] ?>; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;">
                                                <?= $result['candidate']['nomor_urut'] ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <h5 class="mb-1">
                                            <?= htmlspecialchars($result['candidate']['nama_capres']) ?>
                                            &amp; <?= htmlspecialchars($result['candidate']['nama_cawapres']) ?>
                                            <?php if ($isWinning): ?>
                                                <span class="badge bg-success ms-2">
                                                    <i class="fas fa-crown me-1"></i>Unggul
                                                </span>
                                            <?php endif; ?>
                                        </h5>
                                        <small class="text-muted">
                                            <?= htmlspecialchars($result['candidate']['partai_pengusung']) ?>
                                        </small>
                                        <div class="progress mt-2" style="height: 25px; border-radius: 12px;">
                                            <div class="progress-bar" role="progressbar" 
                                                 style="width: <?= $percentage ?>%; background: <?= $images['bg_color'] ?>; border-radius: 12px;">
                                                <strong><?= $percentage ?>%</strong>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-auto text-end">
                                        <h3 class="mb-0" style="color: <?= $images['bg_color'] ?>;"><?= number_format($result['count']) ?></h3>
                                        <small class="text-muted">suara</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php $rank++; endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Info Keamanan -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="fas fa-shield-alt text-success me-2"></i>Keamanan Data Hasil</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 text-center mb-3">
                        <i class="fas fa-lock fa-2x text-warning mb-2"></i>
                        <h6>Data Terenkripsi</h6>
                        <small class="text-muted">Semua suara dienkripsi dengan AES-256</small>
                    </div>
                    <div class="col-md-3 text-center mb-3">
                        <i class="fas fa-check-double fa-2x text-success mb-2"></i>
                        <h6>Integritas Terjamin</h6>
                        <small class="text-muted">Setiap suara memiliki hash SHA-256</small>
                    </div>
                    <div class="col-md-3 text-center mb-3">
                        <i class="fas fa-signature fa-2x text-danger mb-2"></i>
                        <h6>Tanda Tangan Digital</h6>
                        <small class="text-muted">Suara ditandatangani dengan HMAC-SHA256</small>
                    </div>
                    <div class="col-md-3 text-center mb-3">
                        <i class="fas fa-history fa-2x text-info mb-2"></i>
                        <h6>Audit Trail</h6>
                        <small class="text-muted">Semua aktivitas tercatat</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-dark text-white py-3 mt-5">
        <div class="container text-center">
            <small class="text-muted">
                E-Vote Pemilu - Hasil Real Count Terverifikasi
            </small>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Chart.js
        const ctx = document.getElementById('voteChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: [
                    <?php foreach ($results as $r): ?>
                    'Paslon <?= $r['candidate']['nomor_urut'] ?>',
                    <?php endforeach; ?>
                ],
                datasets: [{
                    data: [
                        <?php foreach ($results as $r): ?>
                        <?= $r['count'] ?>,
                        <?php endforeach; ?>
                    ],
                    backgroundColor: [
                        '#dc3545',
                        '#0d6efd',
                        '#198754',
                        '#ffc107',
                        '#6c757d'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>
</body>
</html>
