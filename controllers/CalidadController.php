<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../models/CalidadModel.php';
require_once __DIR__ . '/../models/ProduccionModel.php';

class CalidadController {
    private $model;
    private $produccionModel;

    public function __construct() { 
        $this->model = new CalidadModel(); 
        $this->produccionModel = new ProduccionModel();
    }

    public function procesarAccion() {
        $accion = $_GET['accion'] ?? 'historial';
        switch ($accion) {
            case 'registrar': $this->registrar(); break;
            case 'guardar': $this->guardar(); break;
            case 'editar': $this->editar(); break;
            case 'actualizar': $this->actualizar(); break;
            case 'historial': $this->historial(); break;
            case 'eliminar': $this->eliminar(); break;
        }
    }

    private function registrar() {
        $idLote = $_GET['idLote'] ?? 0;
        $insumosReales = $this->produccionModel->obtenerInsumosRealesPorLote($idLote);
        $lote = $this->produccionModel->obtenerLotePorId($idLote);
        $inspeccion = null;
        
        require_once __DIR__ . '/../views/inspeccion_form.php';
    }

    private function guardar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $idLote = $_POST['idLote'];
            $unidadesDefectuosas = intval($_POST['unidades_defectuosas'] ?? 0);
            
            $lote = $this->produccionModel->obtenerLotePorId($idLote);
            $unidadesDisponiblesActuales = intval($lote['cantidad'] ?? 0);
            $precioUnitario = $lote['precioVenta'] ?? $lote['precio_venta'] ?? $lote['precio'] ?? 0;
            
            $valorBaseDinamico = $unidadesDisponiblesActuales * $precioUnitario;
            
            $balanceInsumos = 0;
            if (!empty($_POST['insumos'])) {
                $insumosRealesOriginales = $this->produccionModel->obtenerInsumosRealesPorLote($idLote);
                foreach ($_POST['insumos'] as $index => $insData) {
                    $cantReal = intval($insData['cantidad'] ?? 0);
                    $cantPlanificada = round($insumosRealesOriginales[$index]['cantidadRequerida'] ?? 0);
                    $costoInsumoTotal = abs($insumosRealesOriginales[$index]['costoInsumo'] ?? 0);
                    $unitCost = ($cantPlanificada != 0) ? ($costoInsumoTotal / abs($cantPlanificada)) : $costoInsumoTotal;
                    
                    $diferenciaUnidades = $cantReal - $cantPlanificada;
                    $balanceInsumos += ($diferenciaUnidades * $unitCost);
                }
                $this->produccionModel->actualizarInsumosRealesLote($idLote, $_POST['insumos']);
            }

            $perdidaDefectos = $unidadesDefectuosas * $precioUnitario;
            
            // Impacto Financiero Neto correcto: Base activa + Insumos - Pérdida por defectos
            $impactoFinancieroNeto = $valorBaseDinamico + $balanceInsumos - $perdidaDefectos;

            $unidadesFinalesCorrectas = max(0, $unidadesDisponiblesActuales - $unidadesDefectuosas);
            $this->produccionModel->actualizarCantidadLote($idLote, $unidadesFinalesCorrectas);

            $this->model->guardarInspeccion(
                $idLote, 
                $_POST['resultado'], 
                $_POST['motivo'], 
                $_POST['observaciones'], 
                $unidadesDefectuosas, 
                $_SESSION['usuario_id'] ?? 1,
                $impactoFinancieroNeto,
                $unidadesDisponiblesActuales
            );
            
            header("Location: /ProQuaris/controllers/CalidadController.php?accion=historial&idLote=$idLote");
            exit();
        }
    }

    private function editar() {
        $idRI = $_GET['id'] ?? 0;
        $inspeccion = $this->model->obtenerInspeccionPorId($idRI);
        if (!$inspeccion) {
            header("Location: /ProQuaris/controllers/ProduccionController.php?accion=listar");
            exit();
        }
        $idLote = $inspeccion['FK_loteId'];
        $lote = $this->produccionModel->obtenerLotePorId($idLote);
        $insumosReales = $this->produccionModel->obtenerInsumosRealesPorLote($idLote);

        require_once __DIR__ . '/../views/inspeccion_form.php';
    }

    private function actualizar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $idRI = $_POST['idRI'];
            $idLote = $_POST['idLote'];
            $unidadesDefectuosas = intval($_POST['unidades_defectuosas'] ?? 0);

            $inspeccionAnterior = $this->model->obtenerInspeccionPorId($idRI);
            $defectosAnteriores = intval($inspeccionAnterior['unidades_defectuosas'] ?? 0);

            $lote = $this->produccionModel->obtenerLotePorId($idLote);
            $unidadesActualesLote = intval($lote['cantidad'] ?? 0);

            $stockBaseRecalculo = $unidadesActualesLote + $defectosAnteriores;
            $precioUnitario = $lote['precioVenta'] ?? $lote['precio_venta'] ?? $lote['precio'] ?? 0;

            $valorBaseDinamico = $stockBaseRecalculo * $precioUnitario;
            $perdidaDefectos = $unidadesDefectuosas * $precioUnitario;
            $impactoFinancieroNeto = $valorBaseDinamico - $perdidaDefectos;

            $nuevasUnidadesLote = max(0, $stockBaseRecalculo - $unidadesDefectuosas);
            $this->produccionModel->actualizarCantidadLote($idLote, $nuevasUnidadesLote);

            if (!empty($_POST['insumos'])) {
                $this->produccionModel->actualizarInsumosRealesLote($idLote, $_POST['insumos']);
            }

            $this->model->actualizarInspeccion(
                $idRI,
                $_POST['resultado'],
                $_POST['motivo'],
                $_POST['observaciones'],
                $unidadesDefectuosas,
                $impactoFinancieroNeto
            );

            header("Location: /ProQuaris/controllers/CalidadController.php?accion=historial&idLote=$idLote");
            exit();
        }
    }

    private function historial() {
        $idLote = $_GET['idLote'] ?? 0;
        $inspecciones = $this->model->obtenerInspeccionesPorLote($idLote);
        require_once __DIR__ . '/../views/inspeccion_historial.php';
    }

    private function eliminar() {
        $idRI = $_GET['id'] ?? 0;
        $idLote = $_GET['idLote'] ?? 0;

        $inspeccion = $this->model->obtenerInspeccionPorId($idRI);
        if ($inspeccion) {
            $defectuosasRegistradas = intval($inspeccion['unidades_defectuosas'] ?? 0);
            $lote = $this->produccionModel->obtenerLotePorId($idLote);
            $stockActual = intval($lote['cantidad'] ?? 0);

            $stockRestaurado = $stockActual + $defectuosasRegistradas;
            $this->produccionModel->actualizarCantidadLote($idLote, $stockRestaurado);
        }

        $this->model->eliminarInspeccion($idRI);
        header("Location: /ProQuaris/controllers/CalidadController.php?accion=historial&idLote=$idLote");
        exit();
    }
}
$controller = new CalidadController();
$controller->procesarAccion();
?>