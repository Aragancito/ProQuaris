<?php
// ==========================================
// CARGA DE DEPENDENCIAS (Orden específico)
// ==========================================
// Se usa __DIR__ para rutas absolutas y evitar conflictos
require_once __DIR__ . '/../config/config.php';   // 1. Credenciales SMTP
require_once __DIR__ . '/../config/conexion.php'; // 2. Conexión a BD (depende de constantes)
require_once __DIR__ . '/enviar_correo.php';       // 3. Función de envío (depende de lo anterior)

// ==========================================
// CAPTURA Y VALIDACIÓN DE DATOS
// ==========================================
$correo = $_POST['correo'] ?? '';

if (empty($correo)) {
    header("Location: ../views/recuperar.php?error=1");
    exit();
}

try {
    // ==========================================
    // VERIFICACIÓN DE EXISTENCIA DEL USUARIO
    // ==========================================
    $db = Conexion::conectar();
    $stmt = $db->prepare("SELECT nombre FROM usuario WHERE correo = ?");
    $stmt->execute([$correo]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        header("Location: ../views/recuperar.php?error=1");
        exit();
    }

    // ==========================================
    // GENERACIÓN DEL TOKEN DE RECUPERACIÓN
    // ==========================================
    // Token criptográficamente seguro de 64 caracteres
    $token = bin2hex(random_bytes(32));
    
    // El token expira en 15 minutos para limitar la ventana de ataque
    $expira = date('Y-m-d H:i:s', strtotime('+15 minutes'));

    // ==========================================
    // PERSISTENCIA DEL TOKEN EN BD
    // ==========================================
    $stmt = $db->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
    $stmt->execute([$correo, $token, $expira]);

    // ==========================================
    // ENVÍO DEL CORREO CON ENLACE DE RECUPERACIÓN
    // ==========================================
    $resultado = enviarCorreo($correo, $usuario['nombre'], $token);

    if ($resultado) {
        header("Location: ../views/recuperar.php?success=1");
    } else {
        // Error SMTP: credenciales incorrectas o bloqueo de Gmail
        header("Location: ../views/recuperar.php?error=mail_failed");
    }
    exit();

} catch (PDOException $e) {
    // Error de BD (ej. tabla password_resets no existe)
    // En producción se redirige sin mostrar detalles técnicos
    // die("Error en Base de Datos: " . $e->getMessage()); // Solo para depuración
    header("Location: ../views/recuperar.php?error=2");
    exit();
}
?>