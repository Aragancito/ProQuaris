<?php
require_once '../config/conexion.php';

$token = $_POST['token'] ?? '';
$contrasena = $_POST['contrasena'] ?? '';
$contrasena_confirm = $_POST['contrasena_confirm'] ?? '';

if (empty($token) || empty($contrasena) || $contrasena !== $contrasena_confirm) {
    header("Location: ../views/restablecer.php?error=2");
    exit();
}

try {
    $db = Conexion::conectar();

    $stmt = $db->prepare("SELECT email FROM password_resets WHERE token = ? AND expires_at > NOW() AND used = FALSE");
    $stmt->execute([$token]);
    $reset = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reset) {
        header("Location: ../views/restablecer.php?error=1");
        exit();
    }

    $hash = password_hash($contrasena, PASSWORD_DEFAULT);
    $stmt = $db->prepare("UPDATE usuario SET contraseña = ? WHERE correo = ?");
    $stmt->execute([$hash, $reset['email']]);

    $stmt = $db->prepare("UPDATE password_resets SET used = TRUE WHERE token = ?");
    $stmt->execute([$token]);

    header("Location: ../views/login.php?recuperado=1");

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>