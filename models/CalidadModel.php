<?php
require_once "../config/conexion.php";

class CalidadModel {
    private $db;

    public function __construct() {
        $this->db = Conexion::conectar();
    }

    public function guardarInspeccion($idLote, $resultado, $motivo, $observaciones, $unidadesDefectuosas, $inspectorId, $impactoFinanciero = 0, $unidadesBase = 0) {
        try {
            $stmtUser = $this->db->prepare("SELECT id FROM usuario WHERE id = :id");
            $stmtUser->execute([':id' => $inspectorId]);
            if (!$stmtUser->fetch()) {
                $fallback = $this->db->query("SELECT id FROM usuario LIMIT 1")->fetch();
                $inspectorId = $fallback['id'] ?? 1;
            }

            $stmtProd = $this->db->prepare("
                SELECT COALESCE(op.producto, 'Producto General') AS productoNombre 
                FROM lote l 
                JOIN ordenproduccion op ON l.FK_ordenId = op.idOrden 
                WHERE l.idLote = :loteId
            ");
            $stmtProd->execute([':loteId' => $idLote]);
            $prodData = $stmtProd->fetch(PDO::FETCH_ASSOC);
            $nombreProducto = $prodData['productoNombre'] ?? 'Producto General';

            $observacionesCompletas = "Motivo: " . $motivo . " - Detalle: " . $observaciones;

            $query = "INSERT INTO registroinspeccion 
                      (FK_loteId, FK_inspectorId, fecha, resultado, observaciones, unidades_defectuosas, producto_nombre, impacto_financiero, unidades_base_inspeccion) 
                      VALUES (:loteId, :inspector, NOW(), :resultado, :obs, :defectuosas, :prodNombre, :impacto, :unidadesBase)";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ':loteId' => $idLote,
                ':inspector' => $inspectorId,
                ':resultado' => $resultado,
                ':obs' => $observacionesCompletas,
                ':defectuosas' => $unidadesDefectuosas,
                ':prodNombre' => $nombreProducto,
                ':impacto' => $impactoFinanciero,
                ':unidadesBase' => $unidadesBase
            ]);
            return true;
        } catch (PDOException $e) {
            die("Error al guardar inspección: " . $e->getMessage());
        }
    }

    public function obtenerInspeccionesPorLote($idLote) {
        try {
            $query = "SELECT r.*, u.nombre AS inspectorNombre, 
                             l.FK_ordenId AS numeroOrden, l.cantidad AS cantidadActualLote,
                             p.precioVenta AS precioUnitarioProducto
                      FROM registroinspeccion r 
                      LEFT JOIN usuario u ON r.FK_inspectorId = u.id 
                      LEFT JOIN lote l ON r.FK_loteId = l.idLote
                      LEFT JOIN ordenproduccion op ON l.FK_ordenId = op.idOrden
                      LEFT JOIN productos p ON op.idProducto = p.idProducto
                      WHERE r.FK_loteId = :loteId 
                      ORDER BY r.fecha DESC, r.idRI DESC";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':loteId' => $idLote]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Error al obtener historial: " . $e->getMessage());
        }
    }

    public function obtenerInspeccionPorId($idRI) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM registroinspeccion WHERE idRI = :id");
            $stmt->execute([':id' => $idRI]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
        }
    }

    public function actualizarInspeccion($idRI, $resultado, $motivo, $observaciones, $unidadesDefectuosas, $impactoFinanciero) {
        try {
            $observacionesCompletas = "Motivo: " . $motivo . " - Detalle: " . $observaciones;
            $stmt = $this->db->prepare("UPDATE registroinspeccion SET resultado = :res, observaciones = :obs, unidades_defectuosas = :def, impacto_financiero = :imp WHERE idRI = :id");
            return $stmt->execute([
                ':res' => $resultado,
                ':obs' => $observacionesCompletas,
                ':def' => $unidadesDefectuosas,
                ':imp' => $impactoFinanciero,
                ':id' => $idRI
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function eliminarInspeccion($idRI) {
        try {
            $stmt = $this->db->prepare("DELETE FROM registroinspeccion WHERE idRI = :id");
            $stmt->execute([':id' => $idRI]);
            return true;
        } catch (PDOException $e) {
            die("Error al eliminar: " . $e->getMessage());
        }
    }
}
?>