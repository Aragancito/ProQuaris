<?php
require_once "../config/conexion.php";

class ProduccionModel {
    private $db;

    public function __construct() {
        $this->db = Conexion::conectar();
    }

    public function obtenerLotes() {
        try {
            $query = "SELECT l.idLote, o.producto, l.cantidad, l.fechaCreacion, l.estado, 
                             COALESCE(r.resultado, 'Sin inspección') AS resultadoCalidad 
                      FROM lote l 
                      JOIN ordenproduccion o ON l.FK_ordenId = o.idOrden
                      LEFT JOIN registroinspeccion r ON l.idLote = r.FK_loteId";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Error al obtener lotes: " . $e->getMessage());
        }
    }

    public function obtenerLotePorId($idLote) {
        try {
            $query = "SELECT * FROM lote WHERE idLote = :idLote";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':idLote' => $idLote]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Error al obtener lote por ID: " . $e->getMessage());
        }
    }

    public function registrarLoteDesdeOrden($fk_ordenId, $cantidad, $estado) {
        try {
            $query = "INSERT INTO lote (FK_ordenId, cantidad, fechaCreacion, estado) 
                      VALUES (:fk_ordenId, :cantidad, CURDATE(), :estado)";
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ':fk_ordenId' => $fk_ordenId,
                ':cantidad' => $cantidad,
                ':estado' => $estado
            ]);
            return true;
        } catch (PDOException $e) {
            die("Error en ProduccionModel: " . $e->getMessage());
        }
    }

    public function actualizarLote($idLote, $fk_ordenId, $cantidad, $estado) {
        try {
            $query = "UPDATE lote SET FK_ordenId = :fk_ordenId, cantidad = :cantidad, estado = :estado WHERE idLote = :idLote";
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ':idLote' => $idLote,
                ':fk_ordenId' => $fk_ordenId,
                ':cantidad' => $cantidad,
                ':estado' => $estado
            ]);
            return true;
        } catch (PDOException $e) {
            die("Error al actualizar lote: " . $e->getMessage());
        }
    }

    public function eliminarLote($idLote) {
        try {
            $query = "DELETE FROM lote WHERE idLote = :idLote";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':idLote' => $idLote]);
            return true;
        } catch (PDOException $e) {
            die("Error al eliminar lote: " . $e->getMessage());
        }
    }
}
?>