<?php
// Usamos __DIR__ para estructurar las cargas en el orden correcto y evitar choques de rutas
require_once __DIR__ . '/../config/config.php';   // 1. Primero cargamos las credenciales globales
require_once __DIR__ . '/../config/conexion.php'; // 2. Luego la conexión que depende de esas credenciales
require_once __DIR__ . '/enviar_correo.php';       // 3. Por último el script que contiene la función del correo

// Obtener el correo del formulario
$correo = $_POST['correo'] ?? '';

// Validar que el correo no esté vacío
if (empty($correo)) {
    header("Location: ../views/recuperar.php?error=1");
    exit();
}

try {
    // Conectar a la base de datos de manera estática
    $db = Conexion::conectar();

    // Buscar el usuario por correo
    $stmt = $db->prepare("SELECT nombre FROM usuario WHERE correo = ?");
    $stmt->execute([$correo]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    // Si el usuario no existe, mostrar error
    if (!$usuario) {
        header("Location: ../views/recuperar.php?error=1");
        exit();
    }

    // Generar un token único seguro y su fecha de expiración
    $token = bin2hex(random_bytes(32));
    $expira = date('Y-m-d H:i:s', strtotime('+15 minutes'));

    // Guardar el token en la tabla correspondiente
    $stmt = $db->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
    $stmt->execute([$correo, $token, $expira]);

    // Enviar el correo llamando a la función configurada con PHPMailer
    $resultado = enviarCorreo($correo, $usuario['nombre'], $token);

    // Redirigir según el resultado del envío
    if ($resultado) {
        header("Location: ../views/recuperar.php?success=1");
    } else {
        // Si falla el envío de correo (Credenciales SMTP incorrectas en config.php)
        header("Location: ../views/recuperar.php?error=mail_failed");
    }
    exit();

} catch (PDOException $e) {
    // Si la base de datos falla (ej. no existe la tabla password_resets), puedes descomentar abajo para depurar:
    // die("Error en Base de Datos: " . $e->getMessage());
    header("Location: ../views/recuperar.php?error=2");
    exit();
}
?>