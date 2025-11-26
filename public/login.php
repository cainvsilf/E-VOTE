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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Email dan password harus diisi';
    } else {
        $result = $auth->login($email, $password);
        if ($result['success']) {
            header('Location: dashboard.php');
            exit();
        } else {
            $error = $result['message'];
        }
    }
}

$flash = getFlashMessage();
if ($flash) {
    if ($flash['type'] === 'success') {
        $success = $flash['message'];
    } else {
        $error = $flash['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - E-Vote Pemilu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-5">
                <div class="text-center mb-4">
                    <a href="index.php" class="text-decoration-none">
                        <h2 class="text-danger">
                            <i class="fas fa-vote-yea me-2"></i>E-Vote Pemilu
                        </h2>
                    </a>
                </div>
                
                <div class="card shadow-lg border-0">
                    <div class="card-header bg-danger text-white text-center py-3">
                        <h4 class="mb-0"><i class="fas fa-sign-in-alt me-2"></i>Masuk</h4>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle me-2"></i><?= $error ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i><?= $success ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope me-1"></i>Email
                                </label>
                                <input type="email" class="form-control form-control-lg" id="email" name="email" 
                                       placeholder="contoh@email.com" required 
                                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                            </div>
                            
                            <div class="mb-4">
                                <label for="password" class="form-label">
                                    <i class="fas fa-lock me-1"></i>Password
                                </label>
                                <div class="input-group">
                                    <input type="password" class="form-control form-control-lg" id="password" 
                                           name="password" placeholder="Masukkan password" required>
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword()">
                                        <i class="fas fa-eye" id="toggleIcon"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-danger btn-lg w-100 mb-3">
                                <i class="fas fa-sign-in-alt me-2"></i>Masuk
                            </button>
                        </form>
                        
                        <div class="text-center">
                            <p class="mb-0">Belum punya akun? 
                                <a href="register.php" class="text-danger fw-bold">Daftar Sekarang</a>
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Info Keamanan -->
                <div class="card mt-3 border-0 bg-white shadow-sm">
                    <div class="card-body">
                        <small class="text-muted">
                            <i class="fas fa-shield-alt text-success me-1"></i>
                            <strong>Keamanan:</strong> Password Anda diverifikasi menggunakan <code>bcrypt</code> 
                            (Layanan Otentikasi)
                        </small>
                    </div>
                </div>
                
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
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
