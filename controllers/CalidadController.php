<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../models/CalidadModel.php';

class CalidadController {
    private $model;

    public function __construct() { $this->model = new CalidadModel(); }

    public function procesarAccion() {
        $accion = $_GET['accion'] ?? 'historial';
        switch ($accion) {
            case 'registrar': require_once __DIR__ . '/../views/inspeccion_form.php'; break;
            case 'guardar': $this->guardar(); break;
            case 'historial': $this->historial(); break;
            case 'eliminar': $this->eliminar(); break;
        }
    }

    private function guardar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $idLote = $_POST['idLote'];
            $this->model->guardarInspeccion($idLote, $_POST['resultado'], $_POST['motivo'], $_POST['observaciones'], $_SESSION['usuario_id'] ?? 1);
            header("Location: /ProQuaris/controllers/CalidadController.php?accion=historial&idLote=$idLote");
        }
    }

    private function historial() {
        $idLote = $_GET['idLote'];
        $inspecciones = $this->model->obtenerInspeccionesPorLote($idLote);
        require_once __DIR__ . '/../views/inspeccion_historial.php';
    }

    private function eliminar() {
        $idLote = $_GET['idLote'];
        $this->model->eliminarInspeccion($_GET['id']);
        header("Location: /ProQuaris/controllers/CalidadController.php?accion=historial&idLote=$idLote");
    }
}
$controller = new CalidadController();
$controller->procesarAccion();
?>