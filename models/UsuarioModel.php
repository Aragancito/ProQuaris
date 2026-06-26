<?php
// ==========================================
// CARGA DE DEPENDENCIAS
// ==========================================
require_once '../config/conexion.php';

// ==========================================
// INTERFAZ DE REPOSITORIO DE USUARIO
// ==========================================
// ABSTRACCIÓN: Define el contrato para cualquier repositorio de usuarios.
// Permite sustituir implementaciones sin afectar al resto del sistema (Dependency Inversion).
interface UsuarioRepositoryInterface {
    public function registrar(array $datos): bool;
    public function buscarPorCorreo(string $correo): ?array;
}

// ==========================================
// REPOSITORIO DE USUARIO (CAPA DE DATOS)
// ==========================================
// S (Single Responsibility): Esta clase tiene UNA ÚNICA responsabilidad:
// interactuar con la base de datos para usuarios.
// La conexión y la creación de tablas se delegan a otras clases.
class UsuarioRepository implements UsuarioRepositoryInterface {
    private $db;

    // D (Dependency Inversion): La conexión se inyecta desde el constructor.
    // Ya no depende de un método estático de Conexion.
    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function registrar(array $datos): bool {
        // Generación de ID única con CSPRNG
        $id = bin2hex(random_bytes(16));
        
        $sql = "INSERT INTO usuario (id, nombre, apellido, correo, contraseña, rol, estado) 
                VALUES (?, ?, ?, ?, ?, ?, 'Activo')";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $id,
            $datos['nombre'],
            $datos['apellido'],
            $datos['correo'],
            $datos['contrasena'],
            $datos['rol']
        ]);
    }

    public function buscarPorCorreo(string $correo): ?array {
        $sql = "SELECT * FROM usuario WHERE correo = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$correo]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado ?: null;
    }
}

// ==========================================
// CONSTRUCTOR DE TABLAS (NUEVA CLASE)
// ==========================================
// S (Single Responsibility): Esta clase solo se encarga de crear tablas.
// Esta responsabilidad estaba mezclada en UsuarioModel original.
class TableCreator {
    private $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    // O (Open/Closed): Se puede extender agregando nuevas tablas
    // sin modificar el código existente.
    public function crearTablaUsuario(): void {
        $sql = "CREATE TABLE IF NOT EXISTS usuario (
            id VARCHAR(36) PRIMARY KEY,
            nombre VARCHAR(30) NOT NULL,
            apellido VARCHAR(30) NOT NULL,
            correo VARCHAR(50) UNIQUE NOT NULL,
            contraseña VARCHAR(255) NOT NULL,
            rol VARCHAR(20) NOT NULL,
            estado VARCHAR(15) DEFAULT 'Activo'
        )";
        $this->db->exec($sql);
    }
}

// ==========================================
// FACTORÍA DE REPOSITORIO
// ==========================================
// Esta clase centraliza la creación de dependencias.
// Facilita la inyección de dependencias en el resto del sistema.
class RepositoryFactory {
    private static $db = null;
    private static $usuarioRepository = null;
    private static $tableCreator = null;

    public static function getDb(): PDO {
        if (self::$db === null) {
            self::$db = Conexion::conectar();
        }
        return self::$db;
    }

    public static function getUsuarioRepository(): UsuarioRepository {
        if (self::$usuarioRepository === null) {
            self::$usuarioRepository = new UsuarioRepository(self::getDb());
        }
        return self::$usuarioRepository;
    }

    public static function getTableCreator(): TableCreator {
        if (self::$tableCreator === null) {
            self::$tableCreator = new TableCreator(self::getDb());
        }
        return self::$tableCreator;
    }

    // O (Open/Closed): Se pueden agregar nuevos repositorios aquí
    // sin modificar el código existente.
}

// ==========================================
// INICIALIZACIÓN DEL SISTEMA
// ==========================================
// En lugar de tener la creación de tablas en el constructor del modelo,
// ahora se llama explícitamente desde un punto de entrada.
// Esto evita que la tabla se cree cada vez que se instancia el modelo.
$tableCreator = RepositoryFactory::getTableCreator();
$tableCreator->crearTablaUsuario();

// Para usar el repositorio en el controlador:
// $usuarioRepo = RepositoryFactory::getUsuarioRepository();
?>