<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_nombre'])) {
    header("Location: /ProQuaris/views/login.php");
    exit();
}

require_once __DIR__ . '/../models/CalidadModel.php';

class CalidadController {
    private $model;

    public function __construct() {
        $this->model = new CalidadModel();
    }

    public function procesarAccion() {
        $accion = $_GET['accion'] ?? 'registrar';

        switch ($accion) {
            case 'registrar':
                $this->registrar();
                break;
            case 'guardar':
                $this->guardar();
                break;
            default:
                header("Location: /ProQuaris/controllers/ProduccionController.php?accion=listar");
                exit();
                break;
        }
    }

    public function registrar() {
        $idLote = $_GET['idLote'] ?? null;
        if (!$idLote) {
            header("Location: /ProQuaris/controllers/ProduccionController.php?accion=listar");
            exit();
        }
        require_once __DIR__ . '/../views/inspeccion_form.php';
    }

    public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $idLote = $_POST['idLote'] ?? null;
            $resultado = $_POST['resultado'] ?? null;
            $motivo = $_POST['motivo'] ?? 'N/A';
            $observaciones = $_POST['observaciones'] ?? '';
            
            // Usamos el ID del usuario de la sesión (asegúrate de que en tu login guardes $_SESSION['usuario_id'])
            $inspectorId = $_SESSION['usuario_id'] ?? 1; 

            if ($idLote && $resultado) {
                $this->model->guardarInspeccion($idLote, $resultado, $motivo, $observaciones, $inspectorId);
                header("Location: /ProQuaris/controllers/ProduccionController.php?accion=listar");
                exit();
            }
        }
    }
}

$controller = new CalidadController();
$controller->procesarAccion();
?>