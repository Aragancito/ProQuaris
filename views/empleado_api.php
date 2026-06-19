<?php
// ==========================================
// VERIFICACIÓN DE SESIÓN Y CONFIGURACIÓN
// ==========================================
session_start();
// La API siempre devuelve respuestas en formato JSON
header('Content-Type: application/json');

// Verifica que el usuario esté autenticado antes de procesar cualquier petición
if (!isset($_SESSION['usuario_nombre'])) {
    echo json_encode(['error' => 'No autorizado']);
    exit();
}

// ==========================================
// CONEXIÓN A LA BASE DE DATOS
// ==========================================
// ABSTRACCIÓN: PDO oculta los detalles de conexión a MySQL.
// El resto del código solo interactúa con métodos de PDO (query, prepare, execute).
try {
    $conn = new PDO("mysql:host=localhost;dbname=proquaris_bd", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(['error' => 'Error BD: ' . $e->getMessage()]);
    exit();
}

// ==========================================
// ENRUTADOR DE ACCIONES
// ==========================================
// POLIMORFISMO: El mismo enrutador maneja diferentes acciones
// según el parámetro recibido (panel, lotes, inspecciones, etc.)
$action = $_GET['action'] ?? $_POST['action'] ?? '';

// ==========================================
// ACCIÓN: PANEL PRINCIPAL
// ==========================================
// Retorna los 5 lotes más recientes, el total de defectos y KPIs básicos
if ($action === 'panel') {
    // POLIMORFISMO: query() se adapta a diferentes consultas SQL
    $stmt = $conn->query("SELECT * FROM lote ORDER BY idLote DESC LIMIT 5");
    $lotes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $stmt2 = $conn->query("SELECT COUNT(*) as total FROM defecto");
    $defectos = $stmt2->fetch(PDO::FETCH_ASSOC);
    
    // ABSTRACCIÓN: Formatea los datos para el frontend sin exponer la estructura de BD
    $lotes_recientes = [];
    foreach ($lotes as $l) {
        $lotes_recientes[] = [
            'id' => $l['idLote'],
            'codigo' => 'LOT-' . $l['idLote'],
            'producto' => 'Producto',
            'cantidad' => $l['cantidad'],
            'estado' => isset($l['estado']) ? $l['estado'] : 'pendiente'
        ];
    }
    
    echo json_encode([
        'lotes_asignados' => count($lotes),
        'defectos_registrados' => $defectos['total'],
        'tareas_pendientes' => 3,
        'lotes_recientes' => $lotes_recientes
    ]);
    
// ==========================================
// ACCIÓN: LISTA DE LOTES
// ==========================================
} elseif ($action === 'lotes' || $action === 'mislotes') {
    $stmt = $conn->query("SELECT * FROM lote ORDER BY idLote DESC");
    $lotes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // ABSTRACCIÓN: Transforma los datos de BD al formato esperado por el frontend
    $resultado = [];
    foreach ($lotes as $l) {
        $resultado[] = [
            'id' => $l['idLote'],
            'codigo' => 'LOT-' . $l['idLote'],
            'producto' => 'Producto',
            'cantidad' => $l['cantidad'],
            'fecha' => isset($l['fechaCreacion']) ? $l['fechaCreacion'] : date('Y-m-d'),
            'estado' => isset($l['estado']) ? $l['estado'] : 'pendiente'
        ];
    }
    echo json_encode($resultado);
    
// ==========================================
// ACCIÓN: INSPECCIONES
// ==========================================
// POLIMORFISMO: LEFT JOIN maneja lotes que no tienen inspecciones
} elseif ($action === 'inspecciones') {
    $stmt = $conn->query("SELECT i.*, l.idLote as lote_codigo FROM registroinspeccion i LEFT JOIN lote l ON i.FK_loteld = l.idLote ORDER BY i.idRI DESC");
    $inspecciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($inspecciones);
    
// ==========================================
// ACCIÓN: DETALLE DE LOTE
// ==========================================
// POLIMORFISMO: La consulta se prepara con un parámetro dinámico
} elseif ($action === 'detalle_lote') {
    $id = isset($_GET['id']) ? $_GET['id'] : 0;
    $stmt = $conn->prepare("SELECT * FROM lote WHERE idLote = ?");
    $stmt->execute([$id]);
    $lote = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($lote) {
        echo json_encode([
            'id' => $lote['idLote'],
            'codigo' => 'LOT-' . $lote['idLote'],
            'producto' => 'Producto',
            'cantidad' => $lote['cantidad'],
            'fecha' => $lote['fechaCreacion'],
            'estado' => $lote['estado']
        ]);
    } else {
        echo json_encode(['error' => 'Lote no encontrado']);
    }
    
// ==========================================
// ACCIÓN: REGISTRAR DEFECTO
// ==========================================
// POLIMORFISMO: La consulta preparada se adapta a diferentes tipos de datos
} elseif ($action === 'registrar_defecto') {
    $lote_id = isset($_POST['lote_id']) ? $_POST['lote_id'] : 0;
    $tipo = isset($_POST['tipo']) ? $_POST['tipo'] : '';
    $severidad = isset($_POST['severidad']) ? $_POST['severidad'] : '';
    $descripcion = isset($_POST['descripcion']) ? $_POST['descripcion'] : '';
    
    // ABSTRACCIÓN: La consulta preparada oculta los detalles de escape de datos
    $stmt = $conn->prepare("INSERT INTO defecto (FK_loteld, tipo, severidad, descripcion) VALUES (?, ?, ?, ?)");
    $resultado = $stmt->execute([$lote_id, $tipo, $severidad, $descripcion]);
    
    echo json_encode(['success' => $resultado]);
    
// ==========================================
// ACCIÓN NO VÁLIDA
// ==========================================
} else {
    echo json_encode(['error' => 'Acción no válida: ' . $action]);
}
?>