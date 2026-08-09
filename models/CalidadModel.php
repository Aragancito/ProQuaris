<?php
require_once "../config/conexion.php";

class CalidadModel {
    private $db;

    public function __construct() {
        $this->db = Conexion::conectar();
    }

    public function guardarInspeccion($idLote, $resultado, $motivo, $observaciones, $inspectorId) {
        try {
            // Combinamos el motivo seleccionado con las observaciones
            $observacionesCompletas = "Motivo: " . $motivo . " - Detalle: " . $observaciones;

            $query = "INSERT INTO registroinspeccion (FK_loteId, FK_inspectorId, fecha, resultado, observaciones) 
                      VALUES (:loteId, :inspector, CURDATE(), :resultado, :obs)";
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ':loteId' => $idLote,
                ':inspector' => $inspectorId,
                ':resultado' => $resultado,
                ':obs' => $observacionesCompletas
            ]);
            
            // Actualizamos el estado del lote principal
            $upd = $this->db->prepare("UPDATE lote SET estado = :estado WHERE idLote = :loteId");
            $upd->execute([':estado' => $resultado, ':loteId' => $idLote]);
            
            return true;
        } catch (PDOException $e) {
            die("Error al guardar inspección: " . $e->getMessage());
        }
    }
}
?>