<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Vote Pemilu Indonesia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        /* Fix untuk tombol bisa diklik */
        .hero-buttons {
            position: relative;
            z-index: 100;
        }
        .hero-buttons a {
            display: inline-block;
            cursor: pointer !important;
            pointer-events: auto !important;
        }
    </style>
</head>
<body>
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
                        <a class="nav-link" href="index.php">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="candidates.php">Kandidat</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="crypto_info.php">Info Kriptografi</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-light text-danger ms-2" href="login.php">
                            <i class="fas fa-sign-in-alt me-1"></i>Masuk
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section text-white text-center py-5" style="background: linear-gradient(135deg, #dc3545, #c82333);">
        <div class="container py-5">
            <div class="row align-items-center">
                <div class="col-lg-6 text-lg-start">
                    <h1 class="display-4 fw-bold mb-4">
                        <i class="fas fa-landmark me-3"></i>E-Vote Pemilu
                    </h1>
                    <p class="lead mb-4">
                        Sistem Pemilihan Elektronik dengan Keamanan Kriptografi Tingkat Tinggi
                    </p>
                    <p class="mb-4">
                        Dilengkapi dengan 4 Layanan Kriptografi: Otentikasi, Integritas, Kerahasiaan, dan Anti-Penyangkalan
                    </p>
                    <div class="d-flex gap-3 justify-content-lg-start justify-content-center hero-buttons">
                        <a href="register.php" class="btn btn-light btn-lg text-danger" style="position: relative; z-index: 999;">
                            <i class="fas fa-user-plus me-2"></i>Daftar Sekarang
                        </a>
                        <a href="login.php" class="btn btn-outline-light btn-lg" style="position: relative; z-index: 999;">
                            <i class="fas fa-sign-in-alt me-2"></i>Masuk
                        </a>
                    </div>
                </div>
                <div class="col-lg-6 mt-5 mt-lg-0">
                    <div class="position-relative" style="z-index: 1;">
                        <!-- Voting Box Illustration -->
                        <div class="text-center">
                            <img src="https://cdn-icons-png.flaticon.com/512/1533/1533931.png" 
                                 alt="E-Vote" class="img-fluid" style="max-height: 300px; filter: drop-shadow(0 10px 30px rgba(0,0,0,0.3));">
                        </div>
                        <!-- Floating Candidate Avatars -->
                        <div class="position-absolute" style="top: 20px; left: 20px;">
                            <img src="https://randomuser.me/api/portraits/men/32.jpg" 
                                 class="rounded-circle shadow" style="width: 60px; height: 60px; border: 3px solid white;">
                        </div>
                        <div class="position-absolute" style="top: 60px; right: 30px;">
                            <img src="https://randomuser.me/api/portraits/men/52.jpg" 
                                 class="rounded-circle shadow" style="width: 50px; height: 50px; border: 3px solid white;">
                        </div>
                        <div class="position-absolute" style="bottom: 40px; left: 50px;">
                            <img src="https://randomuser.me/api/portraits/men/75.jpg" 
                                 class="rounded-circle shadow" style="width: 55px; height: 55px; border: 3px solid white;">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Fitur Keamanan -->
    <section class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center mb-5">
                <i class="fas fa-shield-alt text-danger me-2"></i>4 Layanan Keamanan Kriptografi
            </h2>
            <div class="row g-4">
                <!-- Otentikasi -->
                <div class="col-md-6 col-lg-3">
                    <div class="card h-100 border-0 shadow-sm crypto-card">
                        <div class="card-body text-center p-4">
                            <div class="crypto-icon bg-primary text-white rounded-circle mx-auto mb-3">
                                <i class="fas fa-user-shield"></i>
                            </div>
                            <h5 class="card-title">Otentikasi</h5>
                            <p class="card-text text-muted">
                                <strong>bcrypt Password Hashing</strong><br>
                                Memverifikasi identitas pemilih dengan password yang di-hash menggunakan algoritma bcrypt
                            </p>
                            <span class="badge bg-primary">Authentication</span>
                        </div>
                    </div>
                </div>

                <!-- Integritas -->
                <div class="col-md-6 col-lg-3">
                    <div class="card h-100 border-0 shadow-sm crypto-card">
                        <div class="card-body text-center p-4">
                            <div class="crypto-icon bg-success text-white rounded-circle mx-auto mb-3">
                                <i class="fas fa-check-double"></i>
                            </div>
                            <h5 class="card-title">Integritas</h5>
                            <p class="card-text text-muted">
                                <strong>SHA-256 Hashing</strong><br>
                                Memastikan data suara tidak dapat dimodifikasi setelah disimpan
                            </p>
                            <span class="badge bg-success">Integrity</span>
                        </div>
                    </div>
                </div>

                <!-- Kerahasiaan -->
                <div class="col-md-6 col-lg-3">
                    <div class="card h-100 border-0 shadow-sm crypto-card">
                        <div class="card-body text-center p-4">
                            <div class="crypto-icon bg-warning text-white rounded-circle mx-auto mb-3">
                                <i class="fas fa-lock"></i>
                            </div>
                            <h5 class="card-title">Kerahasiaan</h5>
                            <p class="card-text text-muted">
                                <strong>AES-256-CBC Encryption</strong><br>
                                Data suara dienkripsi sehingga tidak dapat dibaca pihak tidak berwenang
                            </p>
                            <span class="badge bg-warning">Confidentiality</span>
                        </div>
                    </div>
                </div>

                <!-- Anti-Penyangkalan -->
                <div class="col-md-6 col-lg-3">
                    <div class="card h-100 border-0 shadow-sm crypto-card">
                        <div class="card-body text-center p-4">
                            <div class="crypto-icon bg-danger text-white rounded-circle mx-auto mb-3">
                                <i class="fas fa-signature"></i>
                            </div>
                            <h5 class="card-title">Anti-Penyangkalan</h5>
                            <p class="card-text text-muted">
                                <strong>HMAC-SHA256 Digital Signature</strong><br>
                                Pemilih menandatangani suara dengan private key, tidak dapat menyangkal
                            </p>
                            <span class="badge bg-danger">Non-Repudiation</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Cara Kerja -->
    <section class="py-5">
        <div class="container">
            <h2 class="text-center mb-5">
                <i class="fas fa-cogs text-danger me-2"></i>Cara Kerja E-Vote
            </h2>
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-badge bg-danger">1</div>
                            <div class="timeline-content">
                                <h5><i class="fas fa-user-plus me-2"></i>Registrasi</h5>
                                <p>Pemilih mendaftar dengan NIK. Password di-hash dengan <strong>bcrypt</strong>. Sistem generate pasangan kunci HMAC untuk digital signature.</p>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-badge bg-primary">2</div>
                            <div class="timeline-content">
                                <h5><i class="fas fa-sign-in-alt me-2"></i>Login & Otentikasi</h5>
                                <p>Pemilih login dengan email & password. Sistem memverifikasi dengan <strong>password_verify()</strong>.</p>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-badge bg-success">3</div>
                            <div class="timeline-content">
                                <h5><i class="fas fa-vote-yea me-2"></i>Voting</h5>
                                <p>Pemilih memilih kandidat. Data suara di-hash (<strong>SHA-256</strong>), dienkripsi (<strong>AES-256</strong>), dan ditandatangani (<strong>HMAC</strong>).</p>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-badge bg-warning">4</div>
                            <div class="timeline-content">
                                <h5><i class="fas fa-check-circle me-2"></i>Verifikasi</h5>
                                <p>Suara dapat diverifikasi integritasnya dan signature-nya dapat divalidasi menggunakan public key.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Statistik -->
    <section class="py-5 bg-danger text-white">
        <div class="container">
            <div class="row text-center">
                <div class="col-md-4 mb-4 mb-md-0">
                    <i class="fas fa-users fa-3x mb-3"></i>
                    <h2 class="display-5 fw-bold" id="totalVoters">0</h2>
                    <p class="lead">Pemilih Terdaftar</p>
                </div>
                <div class="col-md-4 mb-4 mb-md-0">
                    <i class="fas fa-check-circle fa-3x mb-3"></i>
                    <h2 class="display-5 fw-bold" id="totalVotes">0</h2>
                    <p class="lead">Suara Masuk</p>
                </div>
                <div class="col-md-4">
                    <i class="fas fa-user-tie fa-3x mb-3"></i>
                    <h2 class="display-5 fw-bold">3</h2>
                    <p class="lead">Pasangan Calon</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><i class="fas fa-vote-yea me-2"></i>E-Vote Pemilu</h5>
                    <p class="text-muted">Sistem E-Voting dengan Keamanan Kriptografi</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="text-muted mb-0">
                        <i class="fas fa-shield-alt me-1"></i>
                        Dilindungi dengan: bcrypt | SHA-256 | AES-256 | HMAC-SHA256
                    </p>
                    <small class="text-muted">&copy; 2024 - Tugas Kriptografi</small>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Animasi counter
        function animateCounter(elementId, target) {
            let current = 0;
            const increment = target / 50;
            const element = document.getElementById(elementId);
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    element.textContent = target;
                    clearInterval(timer);
                } else {
                    element.textContent = Math.floor(current);
                }
            }, 30);
        }

        // Jalankan animasi saat halaman dimuat
        document.addEventListener('DOMContentLoaded', () => {
            animateCounter('totalVoters', 150);
            animateCounter('totalVotes', 87);
        });
    </script>
</body>
</html>
