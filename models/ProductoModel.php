<?php
require_once __DIR__ . '/../config/conexion.php';

class ProductoModel {
    private $db;

    public function __construct() {
        $this->db = Conexion::conectar();
    }

    public function obtenerTodos() {
        try {
            $query = "SELECT * FROM productos ORDER BY idProducto DESC";
            return $this->db->query($query)->fetchAll(PDO::FETCH_ASSOC);
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

    public function crearConInsumosDirectos($datosProducto, $insumosDirectos) {
        try {
            $this->db->beginTransaction();

            $query = "INSERT INTO productos (nombre, descripcion, plusvalia, precioVenta) VALUES (?, ?, ?, ?)";
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                $datosProducto['nombre'],
                $datosProducto['descripcion'],
                $datosProducto['plusvalia'],
                $datosProducto['precioVenta']
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