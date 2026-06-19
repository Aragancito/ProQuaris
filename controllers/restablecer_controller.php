<?php
// ==========================================
// CARGA DE DEPENDENCIAS
// ==========================================
require_once '../config/conexion.php';

// ==========================================
// CAPTURA Y VALIDACIÓN DE DATOS DEL FORMULARIO
// ==========================================
// ABSTRACCIÓN: El controlador solo captura datos básicos
// y delega la lógica compleja a la base de datos.
$token = $_POST['token'] ?? '';
$contrasena = $_POST['contrasena'] ?? '';
$contrasena_confirm = $_POST['contrasena_confirm'] ?? '';

// POLIMORFISMO: La validación se adapta según los datos recibidos.
// Si falta el token, la contraseña está vacía o no coinciden.
if (empty($token) || empty($contrasena) || $contrasena !== $contrasena_confirm) {
    header("Location: ../views/restablecer.php?error=2");
    exit();
}

try {
    // ==========================================
    // CONEXIÓN A LA BASE DE DATOS
    // ==========================================
    // ABSTRACCIÓN: Conexion::conectar() oculta los detalles de conexión.
    $db = Conexion::conectar();

    // ==========================================
    // VALIDACIÓN DEL TOKEN
    // ==========================================
    // POLIMORFISMO: La consulta se adapta a diferentes estados del token
    // (existente, no expirado, no usado) sin cambiar el código.
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
    // ABSTRACCIÓN: password_hash() oculta el algoritmo de encriptación.
    // El sistema no sabe cómo se guarda la contraseña, solo que se hashea.
    $hash = password_hash($contrasena, PASSWORD_DEFAULT);
    
    // POLIMORFISMO: La consulta UPDATE se adapta al correo del token.
    $stmt = $db->prepare("UPDATE usuario SET contraseña = ? WHERE correo = ?");
    $stmt->execute([$hash, $reset['email']]);

    // ==========================================
    // MARCA EL TOKEN COMO USADO
    // ==========================================
    // POLIMORFISMO: El token se marca como usado para evitar reutilización.
    $stmt = $db->prepare("UPDATE password_resets SET used = TRUE WHERE token = ?");
    $stmt->execute([$token]);

    // ==========================================
    // REDIRECCIÓN AL LOGIN CON MENSAJE DE ÉXITO
    // ==========================================
    header("Location: ../views/login.php?recuperado=1");

} catch (PDOException $e) {
    // ==========================================
    // MANEJO DE ERRORES
    // ==========================================
    // ABSTRACCIÓN: Los detalles de la excepción se muestran solo
    // en entornos de desarrollo para depuración.
    die("Error: " . $e->getMessage());
}
?>