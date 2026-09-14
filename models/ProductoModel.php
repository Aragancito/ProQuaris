<?php
require_once __DIR__ . '/../config/conexion.php';

class ProductoModel {
    private $db;

    public function __construct() {
        $this->db = Conexion::conectar();
    }

    // $adminId = planta del usuario actual. Si viene vacío, se listan todos
    // (comportamiento anterior; útil para tareas internas).
    public function obtenerTodos($adminId = null) {
        try {
            $sql = "SELECT p.*,
                           (SELECT COUNT(*) FROM productos p2 
                            WHERE p2.admin_id = p.admin_id AND p2.idProducto <= p.idProducto) AS numeroPlanta
                    FROM productos p";
            $params = [];
            if (!empty($adminId)) {
                $sql .= " WHERE p.admin_id = ?";
                $params[] = $adminId;
            }
            $sql .= " ORDER BY p.idProducto DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function obtenerPorId($id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM productos WHERE idProducto = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
        }
    }

    public function obtenerInsumosPorProducto($idProducto) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM recetas WHERE idProducto = ?");
            $stmt->execute([$idProducto]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    // $adminId = planta dueña de este producto (se graba para que obtenerTodos()
    // pueda filtrar por planta más adelante).
    public function crearConInsumosDirectos($datosProducto, $insumosDirectos, $adminId = null) {
        try {
            $this->db->beginTransaction();

            $query = "INSERT INTO productos (nombre, descripcion, plusvalia, precioVenta, admin_id) VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                $datosProducto['nombre'],
                $datosProducto['descripcion'],
                $datosProducto['plusvalia'],
                $datosProducto['precioVenta'],
                $adminId
            ]);
            $idProducto = $this->db->lastInsertId();

            if (!empty($insumosDirectos)) {
                $queryReceta = "INSERT INTO recetas (idProducto, insumo_nombre, cantidadRequerida, costoInsumo, unidad, cantidad_por_empaque, unidad_contenido) VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmtReceta = $this->db->prepare($queryReceta);

                foreach ($insumosDirectos as $ins) {
                    if (!empty($ins['nombre']) && !empty($ins['cantidad'])) {
                        $stmtReceta->execute([
                            $idProducto,
                            $ins['nombre'],
                            $ins['cantidad'],
                            $ins['costo'] ?? 0,
                            $ins['unidad'] ?? 'Unidades',
                            $ins['cantidad_por_empaque'] ?? 1.00,
                            $ins['unidad_contenido'] ?? 'unidades'
                        ]);
                    }
                }
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function actualizar($id, $datosProducto, $insumosDirectos = []) {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("UPDATE productos SET nombre = ?, descripcion = ?, plusvalia = ?, precioVenta = ? WHERE idProducto = ?");
            $stmt->execute([
                $datosProducto['nombre'],
                $datosProducto['descripcion'],
                $datosProducto['plusvalia'],
                $datosProducto['precioVenta'],
                $id
            ]);

            $this->db->prepare("DELETE FROM recetas WHERE idProducto = ?")->execute([$id]);

            if (!empty($insumosDirectos)) {
                $queryReceta = "INSERT INTO recetas (idProducto, insumo_nombre, cantidadRequerida, costoInsumo, unidad, cantidad_por_empaque, unidad_contenido) VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmtReceta = $this->db->prepare($queryReceta);

                foreach ($insumosDirectos as $ins) {
                    if (!empty($ins['nombre']) && !empty($ins['cantidad'])) {
                        $stmtReceta->execute([
                            $id,
                            $ins['nombre'],
                            $ins['cantidad'],
                            $ins['costo'] ?? 0,
                            $ins['unidad'] ?? 'Unidades',
                            $ins['cantidad_por_empaque'] ?? 1.00,
                            $ins['unidad_contenido'] ?? 'unidades'
                        ]);
                    }
                }
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function eliminar($id) {
        try {
            $this->db->beginTransaction();
            $this->db->prepare("DELETE FROM recetas WHERE idProducto = ?")->execute([$id]);
            $this->db->prepare("DELETE FROM productos WHERE idProducto = ?")->execute([$id]);
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
}
?>