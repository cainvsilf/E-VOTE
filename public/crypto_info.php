<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

$auth = new Auth();
$isLoggedIn = $auth->isLoggedIn();

require_once __DIR__ . '/../crypto/Encryption.php';
require_once __DIR__ . '/../crypto/Hashing.php';
require_once __DIR__ . '/../crypto/DigitalSignature.php';

$encryption = new Encryption();
$hashing = new Hashing();
$digitalSignature = new DigitalSignature();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Info Kriptografi - E-Vote Pemilu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .code-block {
            background-color: #000 !important;
            padding: 20px;
            border-radius: 8px;
            overflow-x: auto;
        }
        .code-block pre,
        .code-block pre code,
        .code-block code {
            background-color: #000 !important;
            color: #fff !important;
            font-family: 'Consolas', 'Courier New', monospace !important;
            font-size: 14px !important;
            line-height: 1.5 !important;
            margin: 0;
            padding: 0;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        .code-block .comment {
            color: #0f0 !important;
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

    <div class="container py-5">
        <div class="text-center mb-5">
            <h2><i class="fas fa-book text-danger me-2"></i>Informasi Kriptografi</h2>
            <p class="text-muted">Pelajari bagaimana sistem E-Vote mengamankan suara Anda</p>
        </div>

        <!-- 1. Otentikasi -->
        <div class="card border-0 shadow-sm mb-4" id="authentication">
            <div class="card-header bg-primary text-white py-3">
                <h4 class="mb-0">
                    <i class="fas fa-user-shield me-2"></i>1. Otentikasi (Authentication)
                </h4>
            </div>
            <div class="card-body p-4">
                <div class="row">
                    <div class="col-md-6">
                        <h5>Apa itu Otentikasi?</h5>
                        <p>Otentikasi adalah proses memverifikasi identitas pengguna. 
                        Sistem memastikan bahwa orang yang login benar-benar pemilik akun tersebut.</p>
                        
                        <h5>Algoritma: bcrypt</h5>
                        <p>bcrypt adalah algoritma password hashing yang dirancang khusus untuk menyimpan password dengan aman.</p>
                        
                        <h5>Fungsi PHP yang Digunakan:</h5>
                        <ul>
                            <li><code>password_hash($password, PASSWORD_BCRYPT)</code> - Membuat hash password</li>
                            <li><code>password_verify($password, $hash)</code> - Memverifikasi password</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <div class="code-block">
                            <pre><code><span class="comment">// Contoh penggunaan:</span>

<span class="comment">// Saat registrasi - Hash password</span>
$hashedPassword = password_hash(
    $password, 
    PASSWORD_BCRYPT, 
    ['cost' => 12]
);

<span class="comment">// Saat login - Verifikasi password</span>
if (password_verify($password, $hashedPassword)) {
    echo "Login berhasil!";
}</code></pre>
                        </div>
                        
                        <div class="mt-3 p-3 bg-light rounded">
                            <strong>Keunggulan bcrypt:</strong>
                            <ul class="mb-0 small">
                                <li>Menggunakan salt otomatis</li>
                                <li>Cost factor dapat diatur (semakin tinggi semakin aman)</li>
                                <li>Tahan terhadap rainbow table attack</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Integritas -->
        <div class="card border-0 shadow-sm mb-4" id="integrity">
            <div class="card-header bg-success text-white py-3">
                <h4 class="mb-0">
                    <i class="fas fa-check-double me-2"></i>2. Integritas (Integrity)
                </h4>
            </div>
            <div class="card-body p-4">
                <div class="row">
                    <div class="col-md-6">
                        <h5>Apa itu Integritas?</h5>
                        <p>Integritas memastikan bahwa data tidak diubah atau dimodifikasi tanpa sepengetahuan. 
                        Jika ada perubahan sekecil apapun, sistem akan mendeteksinya.</p>
                        
                        <h5>Algoritma: SHA-256</h5>
                        <p>SHA-256 (Secure Hash Algorithm 256-bit) menghasilkan hash unik 64 karakter heksadesimal 
                        untuk setiap input.</p>
                        
                        <h5>Fungsi PHP yang Digunakan:</h5>
                        <ul>
                            <li><code>hash('sha256', $data)</code> - Membuat hash SHA-256</li>
                            <li><code>hash_equals($hash1, $hash2)</code> - Membandingkan hash dengan aman</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <div class="code-block">
                            <pre><code><span class="comment">// Contoh penggunaan:</span>

<span class="comment">// Hash data vote</span>
$voteData = json_encode([
    'candidate_id' => 1,
    'user_id' => 123,
    'timestamp' => '2024-01-01 10:00:00'
]);

$voteHash = hash('sha256', $voteData);
<span class="comment">// Output: 64 karakter hex</span>

<span class="comment">// Verifikasi integritas</span>
if (hash_equals($storedHash, $calculatedHash)) {
    echo "Data tidak dimodifikasi!";
}</code></pre>
                        </div>
                        
                        <div class="mt-3 p-3 bg-light rounded">
                            <strong>Sifat Hash Function:</strong>
                            <ul class="mb-0 small">
                                <li><strong>One-way:</strong> Tidak bisa dikembalikan ke data asli</li>
                                <li><strong>Deterministic:</strong> Input sama = output sama</li>
                                <li><strong>Collision Resistant:</strong> Hampir mustahil 2 input berbeda menghasilkan hash sama</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Kerahasiaan -->
        <div class="card border-0 shadow-sm mb-4" id="confidentiality">
            <div class="card-header bg-warning text-dark py-3">
                <h4 class="mb-0">
                    <i class="fas fa-lock me-2"></i>3. Kerahasiaan (Confidentiality)
                </h4>
            </div>
            <div class="card-body p-4">
                <div class="row">
                    <div class="col-md-6">
                        <h5>Apa itu Kerahasiaan?</h5>
                        <p>Kerahasiaan memastikan bahwa data hanya dapat dibaca oleh pihak yang berwenang. 
                        Data dienkripsi sehingga tidak dapat dibaca tanpa kunci yang tepat.</p>
                        
                        <h5>Algoritma: AES-256-CBC</h5>
                        <p>AES (Advanced Encryption Standard) dengan kunci 256-bit dan mode CBC 
                        (Cipher Block Chaining) adalah standar enkripsi yang sangat aman.</p>
                        
                        <h5>Fungsi PHP yang Digunakan:</h5>
                        <ul>
                            <li><code>openssl_encrypt()</code> - Mengenkripsi data</li>
                            <li><code>openssl_decrypt()</code> - Mendekripsi data</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <div class="code-block">
                            <pre><code><span class="comment">// Contoh penggunaan:</span>

$method = 'AES-256-CBC';
$key = hash('sha256', 'secret_key', true);
$iv = openssl_random_pseudo_bytes(16);

<span class="comment">// Enkripsi</span>
$encrypted = openssl_encrypt(
    $plaintext,
    $method,
    $key,
    OPENSSL_RAW_DATA,
    $iv
);

<span class="comment">// Dekripsi</span>
$decrypted = openssl_decrypt(
    $encrypted,
    $method,
    $key,
    OPENSSL_RAW_DATA,
    $iv
);</code></pre>
                        </div>
                        
                        <div class="mt-3 p-3 bg-light rounded">
                            <strong>Komponen AES-256-CBC:</strong>
                            <ul class="mb-0 small">
                                <li><strong>Key:</strong> Kunci 256-bit untuk enkripsi/dekripsi</li>
                                <li><strong>IV:</strong> Initialization Vector unik untuk setiap enkripsi</li>
                                <li><strong>CBC Mode:</strong> Setiap blok tergantung blok sebelumnya</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. Non-Repudiation -->
        <div class="card border-0 shadow-sm mb-4" id="non-repudiation">
            <div class="card-header bg-danger text-white py-3">
                <h4 class="mb-0">
                    <i class="fas fa-signature me-2"></i>4. Anti-Penyangkalan (Non-Repudiation)
                </h4>
            </div>
            <div class="card-body p-4">
                <div class="row">
                    <div class="col-md-6">
                        <h5>Apa itu Non-Repudiation?</h5>
                        <p>Non-repudiation memastikan bahwa pengirim tidak dapat menyangkal telah mengirim pesan/data. 
                        Dalam konteks E-Vote, pemilih tidak dapat menyangkal bahwa dia yang memberikan suara.</p>
                        
                        <h5>Algoritma: HMAC-SHA256 Digital Signature</h5>
                        <p>HMAC (Hash-based Message Authentication Code) menggunakan pasangan kunci unik per pengguna. 
                        Private key untuk menandatangani, public key untuk memverifikasi identitas.</p>
                        
                        <h5>Fungsi PHP yang Digunakan:</h5>
                        <ul>
                            <li><code>random_bytes(32)</code> - Generate secret key</li>
                            <li><code>hash_hmac('sha256', $data, $key)</code> - Membuat signature</li>
                            <li><code>hash_equals()</code> - Memverifikasi signature</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <div class="code-block">
                            <pre><code><span class="comment">// Contoh penggunaan:</span>

<span class="comment">// Generate key pair</span>
$secretKey = bin2hex(random_bytes(32));
$keyId = bin2hex(random_bytes(16));

<span class="comment">// Tanda tangan dengan private key (secret)</span>
$dataHash = hash('sha256', $data);
$message = $keyId . '|' . time() . '|' . $dataHash;
$signature = hash_hmac('sha256', $message, 
    $secretKey);

<span class="comment">// Verifikasi - recreate & compare</span>
$expected = hash_hmac('sha256', $message, 
    $secretKey);
$valid = hash_equals($expected, $signature);</code></pre>
                        </div>
                        
                        <div class="mt-3 p-3 bg-light rounded">
                            <strong>Cara Kerja HMAC Signature:</strong>
                            <ul class="mb-0 small">
                                <li><strong>Private Key:</strong> Secret key unik untuk menandatangani</li>
                                <li><strong>Public Key:</strong> Berisi KeyID untuk identifikasi</li>
                                <li><strong>Signature:</strong> HMAC + timestamp + nonce mencegah pemalsuan</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Flow Diagram -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-dark text-white py-3">
                <h4 class="mb-0"><i class="fas fa-project-diagram me-2"></i>Alur Proses Voting</h4>
            </div>
            <div class="card-body p-4">
                <div class="row text-center">
                    <div class="col-md-3 mb-3">
                        <div class="p-3 bg-primary text-white rounded">
                            <i class="fas fa-user-plus fa-2x mb-2"></i>
                            <h6>1. REGISTRASI</h6>
                            <small>Password → bcrypt hash<br>Generate HMAC key pair</small>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="p-3 bg-info text-white rounded">
                            <i class="fas fa-sign-in-alt fa-2x mb-2"></i>
                            <h6>2. LOGIN</h6>
                            <small>password_verify()<br>Otentikasi pengguna</small>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="p-3 bg-success text-white rounded">
                            <i class="fas fa-vote-yea fa-2x mb-2"></i>
                            <h6>3. VOTING</h6>
                            <small>Hash → Encrypt → Sign<br>SHA256 + AES + HMAC</small>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="p-3 bg-warning text-dark rounded">
                            <i class="fas fa-check-circle fa-2x mb-2"></i>
                            <h6>4. VERIFIKASI</h6>
                            <small>Verify hash & signature<br>Decrypt jika perlu</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-dark text-white py-3 mt-5">
        <div class="container text-center">
            <small class="text-muted">
                E-Vote Pemilu - Pembelajaran Kriptografi
            </small>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
