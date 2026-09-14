<?php
if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}
if (!isset($_SESSION['usuario_nombre']) || !in_array($_SESSION['usuario_rol'], ['Administrador', 'Empleado'])) {
    header("Location: /ProQuaris/views/login.php");
    exit();
}

// Validación de planta y aprobación (igual que en los demás módulos).
if ($_SESSION['usuario_rol'] === 'Empleado') {
    if (empty($_SESSION['admin_id']) || ($_SESSION['estado'] ?? '') !== 'Activo') {
        header("Location: /ProQuaris/views/usuarios.php");
        exit();
    }
}

$adminIdPlanta = $_SESSION['admin_id'] ?? null;

require_once __DIR__ . '/../models/InventarioModel.php';

class InventarioController {
    private $model;
    private $adminIdPlanta;

    public function __construct($adminIdPlanta) {
        $this->model = new InventarioModel();
        $this->adminIdPlanta = $adminIdPlanta;
    }

    public function procesarAccion() {
        $accion = $_GET['accion'] ?? 'listar';
        switch ($accion) {
            case 'listar': 
                $this->listar(); 
                break;
            case 'crear': 
                // Solo el Administrador registra insumos de inventario.
                if (($_SESSION['usuario_rol'] ?? '') !== 'Administrador') {
                    header("Location: InventarioController.php?accion=listar");
                    exit();
                }
                $this->crear(); 
                break;
            default: 
                $this->listar(); 
                break;
        }
    }

    public function listar() {
        $insumos = $this->model->obtenerTodos($this->adminIdPlanta);
        require_once __DIR__ . '/../views/inventario.php';
    }

    public function crear() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $datos = [
                'idinventario' => $_POST['idinventario'] ?? null,
                'insumo' => $_POST['insumo'] ?? '',
                'stockActual' => $_POST['stockActual'] ?? 0,
                'ubicacion' => $_POST['ubicacion'] ?? 'Almacén Principal',
                'costoUnitario' => $_POST['costoUnitario'] ?? 0,
                'unidadMedida' => $_POST['unidadMedida'] ?? ''
            ];
            $this->model->insertar($datos, $this->adminIdPlanta);
            header("Location: /ProQuaris/controllers/InventarioController.php?accion=listar");
            exit();
        }
    }
}

$controller = new InventarioController($adminIdPlanta);
$controller->procesarAccion();
?>