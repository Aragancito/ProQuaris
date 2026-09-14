<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['usuario_nombre']) || !in_array($_SESSION['usuario_rol'], ['Administrador', 'Empleado'])) {
    header("Location: ../views/login.php");
    exit();
}

// BLOQUEO ESTRICTO: Si es empleado, debe tener planta asignada Y estar aprobado
if ($_SESSION['usuario_rol'] === 'Empleado') {
    if (empty($_SESSION['admin_id']) || ($_SESSION['estado'] ?? '') !== 'Activo') {
        header("Location: ../views/usuarios.php"); 
        exit();
    }
}

$adminIdPlanta = $_SESSION['admin_id'] ?? null;

require_once __DIR__ . '/../models/ProduccionModel.php';

class ProduccionController {
    private $model;
    private $adminIdPlanta;

    public function __construct($adminIdPlanta) {
        $this->model = new ProduccionModel();
        $this->adminIdPlanta = $adminIdPlanta;
    }

    public function procesarAccion() {
        $accion = $_GET['accion'] ?? 'listar';
        switch ($accion) {
            case 'listar':
                $this->listar();
                break;
            case 'eliminar':
                $this->eliminar();
                break;
            default:
                $this->listar();
                break;
        }
    }

    public function listar() {
        // Solo lotes cuya orden pertenece a la planta del usuario actual
        // (Administrador o Empleado ya aprobado en esa planta).
        $lotes = $this->model->obtenerLotes($this->adminIdPlanta);
        require_once __DIR__ . '/../views/lotes.php';
    }

    public function eliminar() {
        $id = $_GET['id'] ?? null;
        if ($id) {
            $this->model->eliminarLote($id);
        }
        header("Location: ProduccionController.php?accion=listar");
        exit();
    }
}

$controller = new ProduccionController($adminIdPlanta);
$controller->procesarAccion();
?>