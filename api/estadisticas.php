<?php
/**
 * Servicio Web RESTful — ProQuaris API
 * ==========================================
 * Endpoint: GET /ProQuaris/api/estadisticas.php
 *
 * Expone, en formato JSON, las métricas operativas de la planta del
 * usuario autenticado (Administrador o Empleado). Es un servicio REST
 * porque:
 *   - Usa el método HTTP GET para OBTENER un recurso (sin efectos
 *     secundarios en el servidor).
 *   - El recurso solicitado se identifica por la URL, no por parámetros
 *     de sesión ocultos en el body.
 *   - La respuesta es un representación (JSON) del estado actual del
 *     recurso "estadísticas de planta", no una página HTML.
 *   - Es "stateless" en el sentido REST: cada petición se autentica por
 *     sí sola (aquí, mediante la sesión PHP activa del navegador).
 *
 * Pensado para un futuro consumo externo (por ejemplo, una app móvil
 * o un widget embebido), sin depender de que el cliente sepa renderizar
 * las vistas PHP del sistema.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

// --- Autenticación: mismo criterio que el resto del sistema ---
if (!isset($_SESSION['usuario_nombre'])) {
    http_response_code(401); // 401 Unauthorized: código HTTP estándar REST
    echo json_encode([
        'exito' => false,
        'error' => 'No autenticado. Debe iniciar sesión en ProQuaris antes de consumir este servicio.'
    ]);
    exit();
}

require_once __DIR__ . '/../config/conexion.php';
$db = Conexion::conectar();

// Igual que en el dashboard: la planta del usuario (para Administrador
// es su propio id; para Empleado, el admin al que fue aprobado).
$adminIdPlanta = $_SESSION['admin_id'] ?? $_SESSION['usuario_id'] ?? null;

if (empty($adminIdPlanta)) {
    http_response_code(403); // 403 Forbidden: autenticado, pero sin planta asignada
    echo json_encode([
        'exito' => false,
        'error' => 'El usuario no tiene una planta asignada todavía.'
    ]);
    exit();
}

try {
    $stmtActivas = $db->prepare("SELECT COUNT(*) FROM ordenproduccion WHERE estado = 'Activa' AND admin_id = ?");
    $stmtActivas->execute([$adminIdPlanta]);
    $ordenesActivas = (int) $stmtActivas->fetchColumn();

    $stmtLotes = $db->prepare("SELECT COUNT(l.idLote) FROM lote l JOIN ordenproduccion o ON l.FK_ordenId = o.idOrden WHERE o.estado = 'Activa' AND o.admin_id = ?");
    $stmtLotes->execute([$adminIdPlanta]);
    $lotesActivos = (int) $stmtLotes->fetchColumn();

    $stmtAlertas = $db->prepare("SELECT COUNT(*) FROM registroinspeccion WHERE resultado = 'Rechazado' AND admin_id = ?");
    $stmtAlertas->execute([$adminIdPlanta]);
    $alertasCalidad = (int) $stmtAlertas->fetchColumn();

    $stmtInsp = $db->prepare("SELECT COUNT(*) FROM registroinspeccion WHERE admin_id = ?");
    $stmtInsp->execute([$adminIdPlanta]);
    $totalInspecciones = (int) $stmtInsp->fetchColumn();

    // 200 OK es el código de éxito por defecto de PHP; no hace falta setearlo.
    echo json_encode([
        'exito' => true,
        'planta_id' => $adminIdPlanta,
        'consultado_por' => [
            'nombre' => $_SESSION['usuario_nombre'] ?? null,
            'rol' => $_SESSION['usuario_rol'] ?? null
        ],
        'estadisticas' => [
            'ordenes_activas' => $ordenesActivas,
            'lotes_activos' => $lotesActivos,
            'alertas_calidad' => $alertasCalidad,
            'inspecciones_totales' => $totalInspecciones
        ],
        'generado_en' => date('c') // formato ISO 8601, estándar para APIs REST
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500); // 500 Internal Server Error
    echo json_encode([
        'exito' => false,
        'error' => 'Error al consultar la base de datos.'
    ]);
}
?>