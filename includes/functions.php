<?php
/**
 * Helper Functions
 */

require_once __DIR__ . '/../config/database.php';

/**
 * Get all candidates
 */
function getCandidates() {
    $db = getDBConnection();
    $stmt = $db->query("SELECT * FROM candidates ORDER BY nomor_urut ASC");
    return $stmt->fetchAll();
}

/**
 * Get candidate by ID
 */
function getCandidateById($id) {
    $db = getDBConnection();
    $stmt = $db->prepare("SELECT * FROM candidates WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

/**
 * Get vote count per candidate (untuk hasil)
 */
function getVoteResults() {
    $db = getDBConnection();
    
    require_once __DIR__ . '/../crypto/Encryption.php';
    $encryption = new Encryption();
    
    $candidates = getCandidates();
    $results = [];
    
    foreach ($candidates as $candidate) {
        $results[$candidate['id']] = [
            'candidate' => $candidate,
            'count' => 0
        ];
    }
    
    // Decrypt dan hitung suara
    $stmt = $db->query("SELECT encrypted_vote, iv FROM votes");
    $votes = $stmt->fetchAll();
    
    foreach ($votes as $vote) {
        $decrypted = $encryption->decryptVote($vote['encrypted_vote'], $vote['iv']);
        if ($decrypted && isset($decrypted['candidate_id'])) {
            $candidateId = $decrypted['candidate_id'];
            if (isset($results[$candidateId])) {
                $results[$candidateId]['count']++;
            }
        }
    }
    
    return $results;
}

/**
 * Get total votes
 */
function getTotalVotes() {
    $db = getDBConnection();
    $stmt = $db->query("SELECT COUNT(*) as total FROM votes");
    $result = $stmt->fetch();
    return $result['total'];
}

/**
 * Get total registered voters
 */
function getTotalVoters() {
    $db = getDBConnection();
    $stmt = $db->query("SELECT COUNT(*) as total FROM users");
    $result = $stmt->fetch();
    return $result['total'];
}

/**
 * Format timestamp
 */
function formatDate($timestamp) {
    return date('d M Y H:i:s', strtotime($timestamp));
}

/**
 * Sanitize input
 */
function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Redirect dengan pesan
 */
function redirectWithMessage($url, $message, $type = 'info') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
    header("Location: $url");
    exit();
}

/**
 * Get flash message
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'] ?? 'info';
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
        return ['message' => $message, 'type' => $type];
    }
    return null;
}
?>
