<?php
// ==========================================
// CARGA DE DEPENDENCIAS
// ==========================================
require_once '../config/conexion.php';

// ==========================================
// CAPTURA Y VALIDACIÓN DE DATOS DEL FORMULARIO
// ==========================================
$token = $_POST['token'] ?? '';
$contrasena = $_POST['contrasena'] ?? '';
$contrasena_confirm = $_POST['contrasena_confirm'] ?? '';

// Verifica que el token no esté vacío, la contraseña no esté vacía y ambas contraseñas coincidan
if (empty($token) || empty($contrasena) || $contrasena !== $contrasena_confirm) {
    header("Location: ../views/restablecer.php?error=2");
    exit();
}

try {
    // ==========================================
    // CONEXIÓN A LA BASE DE DATOS
    // ==========================================
    $db = Conexion::conectar();

    // ==========================================
    // VALIDACIÓN DEL TOKEN
    // ==========================================
    // Verifica que el token exista, no haya expirado y no haya sido usado previamente
    $stmt = $db->prepare("SELECT email FROM password_resets WHERE token = ? AND expires_at > NOW() AND used = FALSE");
    $stmt->execute([$token]);
    $reset = $stmt->fetch(PDO::FETCH_ASSOC);

    // Si el token es inválido, expirado o ya fue usado, redirige con error
    if (!$reset) {
        header("Location: ../views/restablecer.php?error=1");
        exit();
    }

    // ==========================================
    // ACTUALIZACIÓN DE LA CONTRASEÑA
    // ==========================================
    // Bcrypt es un algoritmo de hash unidireccional recomendado para contraseñas
    $hash = password_hash($contrasena, PASSWORD_DEFAULT);
    
    // Actualiza la contraseña del usuario asociado al correo del token
    $stmt = $db->prepare("UPDATE usuario SET contraseña = ? WHERE correo = ?");
    $stmt->execute([$hash, $reset['email']]);

    // ==========================================
    // MARCA EL TOKEN COMO USADO
    // ==========================================
    // Evita que el mismo token pueda ser reutilizado para otro cambio de contraseña
    $stmt = $db->prepare("UPDATE password_resets SET used = TRUE WHERE token = ?");
    $stmt->execute([$token]);

    // ==========================================
    // REDIRECCIÓN AL LOGIN CON MENSAJE DE ÉXITO
    // ==========================================
    header("Location: ../views/login.php?recuperado=1");

} catch (PDOException $e) {
    // Error en la base de datos: muestra el mensaje para depuración
    die("Error: " . $e->getMessage());
}
?>