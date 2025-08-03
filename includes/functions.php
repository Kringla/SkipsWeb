<?php
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

function is_valid_id($id) {
    // Returnerer true hvis $id er et positivt heltall
    return ctype_digit(strval($id)) && intval($id) > 0;
}

function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}
?>
