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
// ABSTRACCIÓN: El controlador solo captura datos básicos y delega
// la lógica compleja a otros objetos (Conexion, enviarCorreo).
$correo = $_POST['correo'] ?? '';

if (empty($correo)) {
    header("Location: ../views/recuperar.php?error=1");
    exit();
}

try {
    // ==========================================
    // VERIFICACIÓN DE EXISTENCIA DEL USUARIO
    // ==========================================
    // ABSTRACCIÓN: Conexion::conectar() oculta los detalles de conexión.
    // Polimórficamente, la consulta se adapta al correo recibido.
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
    // ABSTRACCIÓN: bin2hex(random_bytes(32)) genera un token seguro.
    // Los detalles criptográficos están ocultos en esta llamada.
    $token = bin2hex(random_bytes(32));
    
    // ABSTRACCIÓN: strtotime() calcula la expiración sin exponer la lógica de tiempo.
    $expira = date('Y-m-d H:i:s', strtotime('+15 minutes'));

    // ==========================================
    // PERSISTENCIA DEL TOKEN EN BD
    // ==========================================
    // POLIMORFISMO: La consulta preparada se adapta a diferentes tipos
    // de datos (string, string, datetime) sin cambiar el código.
    $stmt = $db->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
    $stmt->execute([$correo, $token, $expira]);

    // ==========================================
    // ENVÍO DEL CORREO CON ENLACE DE RECUPERACIÓN
    // ==========================================
    // ABSTRACCIÓN: enviarCorreo() oculta toda la lógica SMTP.
    // El controlador solo recibe un booleano con el resultado.
    $resultado = enviarCorreo($correo, $usuario['nombre'], $token);

    // POLIMORFISMO: El flujo cambia según el resultado del envío.
    if ($resultado) {
        header("Location: ../views/recuperar.php?success=1");
    } else {
        // Error SMTP: credenciales incorrectas o bloqueo de Gmail
        header("Location: ../views/recuperar.php?error=mail_failed");
    }
    exit();

} catch (PDOException $e) {
    // ==========================================
    // MANEJO DE ERRORES
    // ==========================================
    // ABSTRACCIÓN: Los detalles de la excepción se ocultan en producción.
    // Solo se redirige con un código de error genérico.
    header("Location: ../views/recuperar.php?error=2");
    exit();
}
?>