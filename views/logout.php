<?php
// Garantizar el inicio de sesión para acceder al handler
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Vaciar todas las variables globales de sesión
$_SESSION = array();

// 2. Aniquilar la cookie de sesión del navegador enviando parámetros de expiración
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
    
    // Respaldo para limpiar la cookie si fue emitida en la raíz
    setcookie(session_name(), '', time() - 42000, '/');
}

// 3. Destruir la sesión en el servidor
session_destroy();

// 4. Redirigir inmediatamente al formulario de login
header("Location: login.php");
exit();
?>