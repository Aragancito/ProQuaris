<?php
// ==========================================
// CARGA DEL MODELO Y SESIÓN
// ==========================================
// Usamos RepositoryFactory en lugar de UsuarioModel directamente
require_once '../models/RepositoryFactory.php';
session_start();

// ==========================================
// INTERFAZ DE SERVICIO DE AUTENTICACIÓN
// ==========================================
interface AuthServiceInterface {
    public function login(string $correo, string $contraseña): ?array;
    public function registrar(array $datos): bool;
    public function logout(): void;
}

// ==========================================
// SERVICIO DE AUTENTICACIÓN (LÓGICA DE NEGOCIO)
// ==========================================
// S (Single Responsibility): Solo gestiona autenticación.
// D (Dependency Inversion): Depende de la abstracción UsuarioRepositoryInterface.
class AuthService implements AuthServiceInterface {
    private $usuarioRepository;

    // Se inyecta el repositorio (ya no se usa UsuarioModel directamente)
    public function __construct(UsuarioRepositoryInterface $usuarioRepository) {
        $this->usuarioRepository = $usuarioRepository;
    }

    public function login(string $correo, string $contraseña): ?array {
        $usuario = $this->usuarioRepository->buscarPorCorreo($correo);
        
        if ($usuario && password_verify($contraseña, $usuario['contraseña'])) {
            return $usuario;
        }
        return null;
    }

    public function registrar(array $datos): bool {
        $datos['contrasena'] = password_hash($datos['contrasena'], PASSWORD_DEFAULT);
        return $this->usuarioRepository->registrar($datos);
    }

    public function logout(): void {
        $_SESSION = array();
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
        }
        session_destroy();
    }
}

// ==========================================
// CONTROLADOR DE USUARIOS (REFACTORIZADO)
// ==========================================
class UsuarioController {
    private $authService;
    private $routeHandlers;

    public function __construct(AuthServiceInterface $authService) {
        $this->authService = $authService;
        
        $this->routeHandlers = [
            'registrar' => fn() => $this->handleRegistrar(),
            'login'     => fn() => $this->handleLogin(),
            'logout'    => fn() => $this->handleLogout(),
        ];
    }

    public function manejarPeticion(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $accion = $_POST['accion'] ?? 'login';
            $handler = $this->routeHandlers[$accion] ?? $this->routeHandlers['login'];
            $handler();
        } elseif (isset($_GET['logout'])) {
            $this->routeHandlers['logout']();
        }
    }

    private function handleLogin(): void {
        $correo = $_POST['correo'] ?? '';
        $contraseña = $_POST['contraseña'] ?? '';
        
        $usuario = $this->authService->login($correo, $contraseña);
        
        if ($usuario) {
            $_SESSION['usuario_nombre'] = $usuario['nombre'] . " " . $usuario['apellido'];
            $_SESSION['usuario_rol'] = $usuario['rol'];
            
            $dashboard = $usuario['rol'] === 'Administrador' 
                ? '../views/dashboard.php' 
                : '../views/dashboard_empleado.php';
            
            header("Location: $dashboard");
            exit();
        }
        
        header("Location: ../views/login.php?error=1");
        exit();
    }

    private function handleRegistrar(): void {
        $datos = [
            'nombre' => $_POST['nombre'] ?? '',
            'apellido' => $_POST['apellido'] ?? '',
            'correo' => $_POST['correo'] ?? '',
            'contrasena' => $_POST['contrasena'] ?? '',
            'rol' => $_POST['rol'] ?? 'Empleado'
        ];
        
        $resultado = $this->authService->registrar($datos);
        
        $destino = $resultado 
            ? '../views/login.php?registro=exitoso' 
            : '../views/registro.php?error=1';
        
        header("Location: $destino");
        exit();
    }

    private function handleLogout(): void {
        $this->authService->logout();
        header("Location: ../views/index.php");
        exit();
    }
}

// ==========================================
// INSTANCIACIÓN CON INYECCIÓN DE DEPENDENCIAS
// ==========================================
// Usamos RepositoryFactory para obtener el repositorio
$usuarioRepo = RepositoryFactory::getUsuarioRepository();
$authService = new AuthService($usuarioRepo);
$controller = new UsuarioController($authService);
$controller->manejarPeticion();
?>