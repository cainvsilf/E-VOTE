<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

$auth = new Auth();
requireLogin();

$user = $auth->getCurrentUser();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sudah Memilih - E-Vote Pemilu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-6 text-center">
                <div class="card border-0 shadow-lg">
                    <div class="card-body p-5">
                        <i class="fas fa-check-circle text-success fa-5x mb-4"></i>
                        <h2 class="text-success mb-3">Anda Sudah Memilih</h2>
                        <p class="text-muted mb-4">
                            Terima kasih, <?= htmlspecialchars($user['nama']) ?>!<br>
                            Suara Anda telah tercatat dan diamankan dengan kriptografi.
                        </p>
                        <div class="d-grid gap-2">
                            <a href="verify.php" class="btn btn-outline-success btn-lg">
                                <i class="fas fa-search me-2"></i>Verifikasi Suara Saya
                            </a>
                            <a href="results.php" class="btn btn-outline-primary btn-lg">
                                <i class="fas fa-chart-pie me-2"></i>Lihat Hasil
                            </a>
                            <a href="dashboard.php" class="btn btn-danger btn-lg">
                                <i class="fas fa-home me-2"></i>Kembali ke Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
