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

    private function unidadesPlanificadas($lote) {
        $planificadas = intval($lote['cantidadPlanificada'] ?? 0);
        return ($planificadas > 0) ? $planificadas : intval($lote['cantidad'] ?? 0);
    }

    private function calcularImpacto($lote, $insumosPlan, $insumosEnviados, $defectuosasTotales) {
        $planificadas = $this->unidadesPlanificadas($lote);
        $precioUnitario = floatval($lote['precioVenta'] ?? $lote['precio_venta'] ?? $lote['precio'] ?? 0);
        $valorPlanificado = $planificadas * $precioUnitario;

        $planPorId = [];
        foreach ($insumosPlan as $fila) {
            $planPorId[intval($fila['idLoteInsumo'])] = $fila;
        }

        $ajusteInsumos = 0;
        foreach ($insumosEnviados as $insData) {
            $idInsumo = intval($insData['id'] ?? 0);
            if (!isset($planPorId[$idInsumo])) continue;

            $fila = $planPorId[$idInsumo];
            $cantidadPlan = abs(floatval($fila['cantidadRequerida'] ?? 0));
            $costoUnitario = floatval($fila['costoUnitario'] ?? 0);
            if ($costoUnitario == 0 && $cantidadPlan != 0) {
                $costoUnitario = abs(floatval($fila['costoInsumo'] ?? 0)) / $cantidadPlan;
            }

            $cantidadConsumida = max(0, floatval($insData['cantidad'] ?? 0));
            $ajusteInsumos += ($cantidadPlan - $cantidadConsumida) * $costoUnitario;
        }

        $perdidaDefectos = $defectuosasTotales * $precioUnitario;
        $impactoNeto = $valorPlanificado + $ajusteInsumos - $perdidaDefectos;
        $porcentaje = ($valorPlanificado != 0)
            ? (($impactoNeto - $valorPlanificado) / $valorPlanificado) * 100
            : 0;

        return [
            'planificadas' => $planificadas,
            'valorPlanificado' => $valorPlanificado,
            'ajusteInsumos' => $ajusteInsumos,
            'perdidaDefectos' => $perdidaDefectos,
            'impactoNeto' => $impactoNeto,
            'porcentaje' => $porcentaje
        ];
    }

    private function registrar() {
        $idLote = $_GET['idLote'] ?? 0;
        $insumosReales = $this->produccionModel->obtenerInsumosRealesPorLote($idLote);
        $lote = $this->produccionModel->obtenerLotePorId($idLote);
        $defectuosasPrevias = $this->model->sumarDefectuosasPorLote($idLote);
        $inspeccion = null;
        
        require_once __DIR__ . '/../views/inspeccion_form.php';
    }

    private function guardar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $idLote = $_POST['idLote'];
            $insumosEnviados = $_POST['insumos'] ?? [];
            
            $lote = $this->produccionModel->obtenerLotePorId($idLote);
            $insumosPlan = $this->produccionModel->obtenerInsumosRealesPorLote($idLote);
            $planificadas = $this->unidadesPlanificadas($lote);
            $defectuosasPrevias = $this->model->sumarDefectuosasPorLote($idLote);

            // SE TOMAN LOS VALORES EXACTOS DEL FORMULARIO
            $unidadesCorrectas = intval($_POST['unidades_correctas'] ?? 0);
            $unidadesDefectuosas = intval($_POST['unidades_defectuosas'] ?? 0);
            
            // La base de esta inspección es exclusivamente lo que el operario evaluó hoy (ej: 191 + 0 = 191)
            $unidadesBaseInspeccion = $unidadesCorrectas + $unidadesDefectuosas;

            // Las defectuosas totales (históricas + actuales) sirven solo para el cálculo de impacto financiero
            $defectuosasTotales = $defectuosasPrevias + $unidadesDefectuosas;

            $calculo = $this->calcularImpacto($lote, $insumosPlan, $insumosEnviados, $defectuosasTotales);

            if (!empty($insumosEnviados)) {
                $this->produccionModel->actualizarConsumoInsumosLote($idLote, $insumosEnviados);
            }

            $this->produccionModel->actualizarCantidadLote($idLote, max(0, $planificadas - $defectuosasTotales));

            $this->model->guardarInspeccion(
                $idLote, 
                $_POST['resultado'], 
                $_POST['motivo'], 
                $_POST['observaciones'], 
                $unidadesDefectuosas, 
                $_SESSION['usuario_id'] ?? 1,
                $calculo['impactoNeto'],
                $unidadesBaseInspeccion, // <- AQUÍ ENVIAMOS LA BASE REAL (191) EN VEZ DEL TOTAL (200)
                $calculo['porcentaje']
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
        $defectuosasPrevias = $this->model->sumarDefectuosasPorLote($idLote, $idRI);

        require_once __DIR__ . '/../views/inspeccion_form.php';
    }

    private function actualizar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $idRI = $_POST['idRI'];
            $idLote = $_POST['idLote'];
            $insumosEnviados = $_POST['insumos'] ?? [];

            $lote = $this->produccionModel->obtenerLotePorId($idLote);
            $insumosPlan = $this->produccionModel->obtenerInsumosRealesPorLote($idLote);
            $planificadas = $this->unidadesPlanificadas($lote);
            $defectuosasOtrasInspecciones = $this->model->sumarDefectuosasPorLote($idLote, $idRI);

            // SE TOMAN LOS VALORES EXACTOS DEL FORMULARIO
            $unidadesCorrectas = intval($_POST['unidades_correctas'] ?? 0);
            $unidadesDefectuosas = intval($_POST['unidades_defectuosas'] ?? 0);
            
            $unidadesBaseInspeccion = $unidadesCorrectas + $unidadesDefectuosas; 

            $defectuosasTotales = $defectuosasOtrasInspecciones + $unidadesDefectuosas;

            $calculo = $this->calcularImpacto($lote, $insumosPlan, $insumosEnviados, $defectuosasTotales);

            if (!empty($insumosEnviados)) {
                $this->produccionModel->actualizarConsumoInsumosLote($idLote, $insumosEnviados);
            }

            $this->produccionModel->actualizarCantidadLote($idLote, max(0, $planificadas - $defectuosasTotales));

            $this->model->actualizarInspeccion(
                $idRI,
                $_POST['resultado'],
                $_POST['motivo'],
                $_POST['observaciones'],
                $unidadesDefectuosas,
                $calculo['impactoNeto'],
                $unidadesBaseInspeccion, // <- AQUÍ ENVIAMOS LA BASE REAL
                $calculo['porcentaje']
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

        $this->model->eliminarInspeccion($idRI);

        $lote = $this->produccionModel->obtenerLotePorId($idLote);
        if ($lote) {
            $planificadas = $this->unidadesPlanificadas($lote);
            $defectuosasRestantes = $this->model->sumarDefectuosasPorLote($idLote);
            $this->produccionModel->actualizarCantidadLote($idLote, max(0, $planificadas - $defectuosasRestantes));
        }
        header("Location: /ProQuaris/controllers/CalidadController.php?accion=historial&idLote=$idLote");
        exit();
    }
}
$controller = new CalidadController();
$controller->procesarAccion();
?>