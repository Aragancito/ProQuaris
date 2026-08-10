<?php
require_once "../config/conexion.php";

class ProduccionModel {
    private $db;

    public function __construct() {
        $this->db = Conexion::conectar();
    }

    public function obtenerLotes() {
        try {
            $query = "SELECT l.idLote, l.FK_ordenId, o.producto, l.cantidad, l.fechaCreacion, l.estado, 
                             (SELECT r.resultado FROM registroinspeccion r 
                              WHERE r.FK_loteId = l.idLote 
                              ORDER BY r.fecha DESC LIMIT 1) AS resultadoCalidad 
                      FROM lote l 
                      JOIN ordenproduccion o ON l.FK_ordenId = o.idOrden";
            return $this->db->query($query)->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) { 
            die("Error de base de datos: " . $e->getMessage()); 
        }
    }

    public function obtenerLotePorId($idLote) {
        $stmt = $this->db->prepare("SELECT * FROM lote WHERE idLote = :id");
        $stmt->execute([':id' => $idLote]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function registrarLote($fk_ordenId, $cantidad, $estado) {
        try {
            $query = "INSERT INTO lote (FK_ordenId, cantidad, fechaCreacion, estado) VALUES (:fk, :cant, CURDATE(), :est)";
            $stmt = $this->db->prepare($query);
            return $stmt->execute([':fk' => $fk_ordenId, ':cant' => $cantidad, ':est' => $estado]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function actualizarLote($idLote, $fk_ordenId, $cantidad, $estado) {
        $query = "UPDATE lote SET FK_ordenId = :fk, cantidad = :cant, estado = :est WHERE idLote = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([':fk' => $fk_ordenId, ':cant' => $cantidad, ':est' => $estado, ':id' => $idLote]);
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

    public function eliminarLote($idLote) {
        $stmt = $this->db->prepare("DELETE FROM lote WHERE idLote = :id");
        return $stmt->execute([':id' => $idLote]);
    }
}
?>