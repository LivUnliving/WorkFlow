<?php
function tokenCsrf() {
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}
function verificarCsrf($token) {
    return is_string($token) && hash_equals(tokenCsrf(), $token);
}
function exigirPerfil($tipo) {
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    if (empty($_SESSION['usuario_id']) || ($_SESSION['usuario_tipo'] ?? '') !== $tipo) {
        header('Location: Login.php'); exit;
    }
}
function esc($texto) { return htmlspecialchars((string)$texto, ENT_QUOTES, 'UTF-8'); }
?>
