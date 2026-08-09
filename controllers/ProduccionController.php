<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_nombre'])) {
    header("Location: /ProQuaris/views/login.php");
    exit();
}

require_once __DIR__ . '/../models/ProduccionModel.php';

class ProduccionController {
    private $model;

    public function __construct() {
        $this->model = new ProduccionModel();
    }

    public function procesarAccion() {
        $accion = $_GET['accion'] ?? 'listar';

        switch ($accion) {
            case 'listar':
                $this->listar();
                break;
            case 'crear':
                $this->crear();
                break;
            case 'guardar':
                $this->guardar();
                break;
            case 'editar':
                $this->editar();
                break;
            case 'actualizar':
                $this->actualizar();
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
        $lotes = $this->model->obtenerLotes();
        require_once __DIR__ . '/../views/lotes.php';
    }

    public function crear() {
        $loteActual = null;
        require_once __DIR__ . '/../views/lote_form.php';
    }

    public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ordenId = $_POST['orden_id'] ?? null;
            $cantidad = $_POST['cantidad'] ?? null;
            $estado = $_POST['estado'] ?? 'Activa';

            if ($ordenId && $cantidad) {
                $this->model->registrarLoteDesdeOrden($ordenId, $cantidad, $estado);
                header("Location: /ProQuaris/controllers/ProduccionController.php?accion=listar");
                exit();
            }
        }
    }

    public function editar() {
        $id = $_GET['id'] ?? null;
        if ($id) {
            $loteActual = $this->model->obtenerLotePorId($id);
            if ($loteActual) {
                require_once __DIR__ . '/../views/lote_form.php';
                return;
            }
        }
        header("Location: /ProQuaris/controllers/ProduccionController.php?accion=listar");
        exit();
    }

    public function actualizar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $idLote = $_POST['idLote'] ?? null;
            $ordenId = $_POST['orden_id'] ?? null;
            $cantidad = $_POST['cantidad'] ?? null;
            $estado = $_POST['estado'] ?? 'Activa';

            if ($idLote && $ordenId && $cantidad) {
                $this->model->actualizarLote($idLote, $ordenId, $cantidad, $estado);
                header("Location: /ProQuaris/controllers/ProduccionController.php?accion=listar");
                exit();
            }
        }
    }

    public function eliminar() {
        $id = $_GET['id'] ?? null;
        if ($id) {
            $this->model->eliminarLote($id);
            header("Location: /ProQuaris/controllers/ProduccionController.php?accion=listar");
            exit();
        }
    }
}

$controller = new ProduccionController();
$controller->procesarAccion();
?>