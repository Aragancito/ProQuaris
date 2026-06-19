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
// Determina qué acción ejecutar según el parámetro recibido (GET o POST)
$action = $_GET['action'] ?? $_POST['action'] ?? '';

// ==========================================
// ACCIÓN: PANEL PRINCIPAL
// ==========================================
// Retorna los 5 lotes más recientes, el total de defectos y KPIs básicos
if ($action === 'panel') {
    // Obtiene los 5 lotes más recientes ordenados por ID descendente
    $stmt = $conn->query("SELECT * FROM lote ORDER BY idLote DESC LIMIT 5");
    $lotes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Cuenta el total de defectos registrados
    $stmt2 = $conn->query("SELECT COUNT(*) as total FROM defecto");
    $defectos = $stmt2->fetch(PDO::FETCH_ASSOC);
    
    // Formatea los datos de los lotes para el frontend
    $lotes_recientes = [];
    foreach ($lotes as $l) {
        $lotes_recientes[] = [
            'id' => $l['idLote'],
            'codigo' => 'LOT-' . $l['idLote'],
            'producto' => 'Producto', // Valor por defecto (debería venir de la BD)
            'cantidad' => $l['cantidad'],
            'estado' => isset($l['estado']) ? $l['estado'] : 'pendiente'
        ];
    }
    
    echo json_encode([
        'lotes_asignados' => count($lotes),
        'defectos_registrados' => $defectos['total'],
        'tareas_pendientes' => 3, // Valor fijo de ejemplo
        'lotes_recientes' => $lotes_recientes
    ]);
    
// ==========================================
// ACCIÓN: LISTA DE LOTES
// ==========================================
// Retorna todos los lotes ordenados por ID descendente
} elseif ($action === 'lotes' || $action === 'mislotes') {
    $stmt = $conn->query("SELECT * FROM lote ORDER BY idLote DESC");
    $lotes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
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
// Retorna todas las inspecciones con el código del lote asociado
} elseif ($action === 'inspecciones') {
    // LEFT JOIN para incluir lotes sin inspecciones si existieran
    $stmt = $conn->query("SELECT i.*, l.idLote as lote_codigo FROM registroinspeccion i LEFT JOIN lote l ON i.FK_loteld = l.idLote ORDER BY i.idRI DESC");
    $inspecciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($inspecciones);
    
// ==========================================
// ACCIÓN: DETALLE DE LOTE
// ==========================================
// Retorna la información completa de un lote específico por su ID
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
// Inserta un nuevo defecto asociado a un lote
} elseif ($action === 'registrar_defecto') {
    $lote_id = isset($_POST['lote_id']) ? $_POST['lote_id'] : 0;
    $tipo = isset($_POST['tipo']) ? $_POST['tipo'] : '';
    $severidad = isset($_POST['severidad']) ? $_POST['severidad'] : '';
    $descripcion = isset($_POST['descripcion']) ? $_POST['descripcion'] : '';
    
    // Consulta preparada para prevenir inyección SQL
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