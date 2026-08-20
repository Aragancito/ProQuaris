<?php
require_once '../config/conexion.php';

class OrdenModel {
    private $db;

    public function __construct() {
        $this->db = Conexion::conectar();
    }

    public function obtenerTodas() {
        $sql = "SELECT o.*, p.nombre AS producto 
                FROM ordenproduccion o 
                LEFT JOIN productos p ON o.idProducto = p.idProducto 
                ORDER BY o.idOrden DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorId($id) {
        $sql = "SELECT * FROM ordenproduccion WHERE idOrden = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function crear($datos) {
        try {
            $this->db->beginTransaction();

            $sql = "INSERT INTO ordenproduccion (cantidadPlanificada, fechaInicio, idProducto, estado) 
                    VALUES (?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $datos['cantidadPlanificada'],
                $datos['fechaInicio'],
                $datos['idProducto'],
                'Activa'
            ]);
            $idOrden = $this->db->lastInsertId();

            $this->descontarInventario($datos['idProducto'], $datos['cantidadPlanificada']);

            $this->db->commit();
            return $idOrden;

        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    private function descontarInventario($idProducto, $cantidadPlanificada) {
        $sqlReceta = "SELECT idinventario, cantidadRequerida FROM recetas WHERE idProducto = ?";
        $stmtReceta = $this->db->prepare($sqlReceta);
        $stmtReceta->execute([$idProducto]);
        $receta = $stmtReceta->fetchAll(PDO::FETCH_ASSOC);

        foreach ($receta as $item) {
            $totalDescontar = $item['cantidadRequerida'] * $cantidadPlanificada;
            $sqlStock = "UPDATE inventario SET stockActual = stockActual - ? WHERE idinventario = ?";
            $stmtStock = $this->db->prepare($sqlStock);
            $stmtStock->execute([$totalDescontar, $item['idinventario']]);
        }
    }

    private function devolverInventario($idProducto, $cantidadPlanificada) {
        $sqlReceta = "SELECT idinventario, cantidadRequerida FROM recetas WHERE idProducto = ?";
        $stmtReceta = $this->db->prepare($sqlReceta);
        $stmtReceta->execute([$idProducto]);
        $receta = $stmtReceta->fetchAll(PDO::FETCH_ASSOC);

        foreach ($receta as $item) {
            $totalDevolver = $item['cantidadRequerida'] * $cantidadPlanificada;
            $sqlStock = "UPDATE inventario SET stockActual = stockActual + ? WHERE idinventario = ?";
            $stmtStock = $this->db->prepare($sqlStock);
            $stmtStock->execute([$totalDevolver, $item['idinventario']]);
        }
    }

    public function actualizar($id, $datosNuevos) {
        try {
            $this->db->beginTransaction();

            $ordenAntigua = $this->obtenerPorId($id);
            $estadoAntiguo = $ordenAntigua['estado'] ?? '';

            $sql = "UPDATE ordenproduccion SET 
                    cantidadPlanificada = ?,
                    fechaInicio = ?,
                    idProducto = ?,
                    estado = ?
                    WHERE idOrden = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $datosNuevos['cantidadPlanificada'],
                $datosNuevos['fechaInicio'],
                $datosNuevos['idProducto'],
                $datosNuevos['estado'],
                $id
            ]);

            if ($estadoAntiguo !== 'Inactiva' && $datosNuevos['estado'] === 'Inactiva') {
                $this->devolverInventario($datosNuevos['idProducto'], $datosNuevos['cantidadPlanificada']);
            } 
            else if ($estadoAntiguo === 'Inactiva' && $datosNuevos['estado'] !== 'Inactiva') {
                $this->descontarInventario($datosNuevos['idProducto'], $datosNuevos['cantidadPlanificada']);
            }

            if ($datosNuevos['estado'] === 'Completada') {
                $this->registrarEnHistorico($id);
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    private function registrarEnHistorico($idOrden) {
        try {
            $stmtCheck = $this->db->prepare("SELECT idHistorico FROM historico_produccion WHERE idOrden = ?");
            $stmtCheck->execute([$idOrden]);
            if ($stmtCheck->fetch()) return;

            $sqlInfo = "SELECT o.idOrden, p.nombre AS productoNombre, o.cantidadPlanificada, 
                               (SELECT SUM(unidades_defectuosas) FROM registroinspeccion r JOIN lote lt ON r.FK_loteId = lt.idLote WHERE lt.FK_ordenId = o.idOrden) as totalDefectuosas,
                               (SELECT impacto_financiero FROM registroinspeccion r JOIN lote lt ON r.FK_loteId = lt.idLote WHERE lt.FK_ordenId = o.idOrden ORDER BY r.fecha DESC, r.idRI DESC LIMIT 1) as ultimoImpacto
                        FROM ordenproduccion o
                        LEFT JOIN productos p ON o.idProducto = p.idProducto
                        WHERE o.idOrden = ?";
            $stmtInfo = $this->db->prepare($sqlInfo);
            $stmtInfo->execute([$idOrden]);
            $info = $stmtInfo->fetch(PDO::FETCH_ASSOC);

            if ($info) {
                $sqlHist = "INSERT INTO historico_produccion (idOrden, productoNombre, cantidadPlanificada, unidadesCorrectas, unidadesDefectuosas, impactoFinancieroNeto) 
                            VALUES (?, ?, ?, ?, ?, ?)";
                $stmtHist = $this->db->prepare($sqlHist);
                $stmtHist->execute([
                    $idOrden, 
                    $info['productoNombre'], 
                    $info['cantidadPlanificada'], 
                    ($info['cantidadPlanificada'] - ($info['totalDefectuosas'] ?? 0)), 
                    ($info['totalDefectuosas'] ?? 0), 
                    ($info['ultimoImpacto'] ?? 0)
                ]);
            }
        } catch (Exception $e) {}
    }

    public function obtenerHistoricoCompleto() {
        $sql = "SELECT h.*, l.idLote 
                FROM historico_produccion h 
                LEFT JOIN lote l ON h.idOrden = l.FK_ordenId 
                ORDER BY h.idHistorico DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function eliminar($id) {
        try {
            $this->db->beginTransaction();
            $this->db->prepare("DELETE FROM ordenproduccion WHERE idOrden = ?")->execute([$id]);
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
}
?>