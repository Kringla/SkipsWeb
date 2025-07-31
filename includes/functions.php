<?php
// functions.php
session_start();

/**
 * Sjekker om bruker er logget inn
 * @return bool
 */
function isLoggedIn(): bool {
    return !empty($_SESSION['user_id']);
}

/**
 * Sjekker om bruker er administrator (ADM)
 * @return bool
 */
function isAdmin(): bool {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'ADM';
}

/**
 * Krever at bruker er logget inn; ellers redirect til login
 */
function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Krever at bruker er ADM; viser feilmelding hvis ikke
 */
function requireAdmin(): void {
    requireLogin();
    if (!isAdmin()) {
        http_response_code(403);
        echo 'Du har ikke tilgang til denne siden.';
        exit;
    }
}
