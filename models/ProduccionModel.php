<?php
require_once __DIR__ . '/../config/conexion.php';

class ProduccionModel {
    private $db;

    public function __construct() {
        $this->db = Conexion::conectar();
    }

    public function obtenerLotes() {
        try {
            $query = "SELECT l.idLote, l.FK_ordenId, p.nombre AS producto, l.cantidad, l.fechaCreacion, l.estado, 
                            (SELECT r.resultado FROM registroinspeccion r 
                             WHERE r.FK_loteId = l.idLote 
                             ORDER BY r.fecha DESC LIMIT 1) AS resultadoCalidad 
                      FROM lote l 
                      JOIN ordenproduccion o ON l.FK_ordenId = o.idOrden
                      LEFT JOIN productos p ON o.idProducto = p.idProducto
                      WHERE o.estado != 'Completada'
                      ORDER BY l.idLote DESC";
            return $this->db->query($query)->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) { 
            die("Error de base de datos: " . $e->getMessage()); 
        }
    }

    public function obtenerLotePorId($idLote) {
        $stmt = $this->db->prepare("SELECT l.*, o.cantidadPlanificada, p.nombre AS producto, p.idProducto, p.precioVenta FROM lote l 
                                    JOIN ordenproduccion o ON l.FK_ordenId = o.idOrden
                                    LEFT JOIN productos p ON o.idProducto = p.idProducto
                                    WHERE l.idLote = :id");
        $stmt->execute([':id' => $idLote]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function obtenerInsumosRealesPorLote($idLote) {
        $stmt = $this->db->prepare("SELECT * FROM lote_insumos_reales WHERE FK_loteId = ?");
        $stmt->execute([$idLote]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function registrarLote($fk_ordenId, $cantidad, $estado) {
        try {
            $this->db->beginTransaction();

            $query = "INSERT INTO lote (FK_ordenId, cantidad, fechaCreacion, estado) VALUES (:fk, :cant, CURDATE(), :est)";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':fk' => $fk_ordenId, ':cant' => $cantidad, ':est' => $estado]);
            $idLoteGenerado = $this->db->lastInsertId();

            $stmtOrden = $this->db->prepare("SELECT idProducto FROM ordenproduccion WHERE idOrden = ?");
            $stmtOrden->execute([$fk_ordenId]);
            $orden = $stmtOrden->fetch(PDO::FETCH_ASSOC);

            if ($orden && !empty($orden['idProducto'])) {
                $stmtRecetas = $this->db->prepare("SELECT * FROM recetas WHERE idProducto = ?");
                $stmtRecetas->execute([$orden['idProducto']]);
                $recetas = $stmtRecetas->fetchAll(PDO::FETCH_ASSOC);

                $stmtIns = $this->db->prepare("INSERT INTO lote_insumos_reales (FK_loteId, insumo_nombre, cantidadRequerida, costoInsumo, unidad, cantidad_por_empaque, unidad_contenido) VALUES (?, ?, ?, ?, ?, ?, ?)");
                foreach ($recetas as $rec) {
                    $stmtIns->execute([
                        $idLoteGenerado,
                        $rec['insumo_nombre'],
                        $rec['cantidadRequerida'],
                        $rec['costoInsumo'],
                        $rec['unidad'] ?? 'Unidades',
                        $rec['cantidad_por_empaque'] ?? 1.00,
                        $rec['unidad_contenido'] ?? 'unidades'
                    ]);
                }
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function actualizarEstadoPorOrden($fk_ordenId, $estado) {
        try {
            $query = "UPDATE lote SET estado = :est WHERE FK_ordenId = :fk";
            $stmt = $this->db->prepare($query);
            return $stmt->execute([':est' => $estado, ':fk' => $fk_ordenId]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function actualizarCantidadLote($idLote, $nuevaCantidad) {
        try {
            $stmt = $this->db->prepare("UPDATE lote SET cantidad = :cantidad WHERE idLote = :idLote");
            return $stmt->execute([
                ':cantidad' => $nuevaCantidad,
                ':idLote' => $idLote
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function actualizarInsumosRealesLote($idLote, $insumosReales) {
        try {
            $this->db->beginTransaction();
            $this->db->prepare("DELETE FROM lote_insumos_reales WHERE FK_loteId = ?")->execute([$idLote]);

            $stmtIns = $this->db->prepare("INSERT INTO lote_insumos_reales (FK_loteId, insumo_nombre, cantidadRequerida, costoInsumo, unidad, cantidad_por_empaque, unidad_contenido) VALUES (?, ?, ?, ?, ?, ?, ?)");
            foreach ($insumosReales as $ins) {
                if (!empty($ins['nombre'])) {
                    $stmtIns->execute([
                        $idLote,
                        $ins['nombre'],
                        $ins['cantidad'] ?? 0,
                        $ins['costo'] ?? 0,
                        $ins['unidad'] ?? 'Unidades',
                        $ins['cantidad_por_empaque'] ?? 1.00,
                        $ins['unidad_contenido'] ?? 'unidades'
                    ]);
                }
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function eliminarLote($idLote) {
        try {
            $this->db->beginTransaction();

            $stmtLote = $this->db->prepare("SELECT FK_ordenId FROM lote WHERE idLote = ?");
            $stmtLote->execute([$idLote]);
            $loteData = $stmtLote->fetch(PDO::FETCH_ASSOC);
            $idOrden = $loteData['FK_ordenId'] ?? null;

            $this->db->prepare("DELETE FROM lote_insumos_reales WHERE FK_loteId = ?")->execute([$idLote]);
            $this->db->prepare("DELETE FROM registroinspeccion WHERE FK_loteId = ?")->execute([$idLote]);

            $stmt = $this->db->prepare("DELETE FROM lote WHERE idLote = :id");
            $stmt->execute([':id' => $idLote]);

            if ($idOrden) {
                $stmtOrden = $this->db->prepare("DELETE FROM ordenproduccion WHERE idOrden = ?");
                $stmtOrden->execute([$idOrden]);
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
}
?>