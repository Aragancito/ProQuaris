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

// 4. Redirigir inmediatamente al index (landing page), no al login directo.
// logout.php vive en /views/, así que hay que subir un nivel para llegar
// a index.php, que está en la raíz del proyecto. Aplica igual para
// Administrador y Empleado: no hay distinción de rol en este archivo.
header("Location: ../index.php");
exit();
?>