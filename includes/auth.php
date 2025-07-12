
<?php
function isAuthenticated() {
    return isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0;
}

function isAdmin() {
    return isAuthenticated() && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function requireAuth($role = 'user') {
    if (!isAuthenticated()) {
        header('Location: /login');
        exit;
    }
    if ($role === 'admin' && !isAdmin()) {
        header('Location: /dashboard');
        exit;
    }
}

function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
?>
