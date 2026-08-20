<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../models/UsuarioModel.php';

class AuthService {
    private $usuarioModel;

    public function __construct($usuarioModel) {
        $this->usuarioModel = $usuarioModel;
    }

    public function login($correo, $contraseña) {
        $usuario = $this->usuarioModel->buscarPorCorreo($correo);
        
        if ($usuario) {
            $hashAlmacenado = $usuario['contraseña'] ?? $usuario['password'] ?? '';
            if (password_verify($contraseña, $hashAlmacenado)) {
                return $usuario;
            }
        }
        return null;
    }

    public function registrar($datos) {
        $datos['contraseña'] = password_hash($datos['contraseña'], PASSWORD_DEFAULT);
        return $this->usuarioModel->registrarUsuario($datos);
    }

    public function actualizarAdminAsignado($idUsuario, $adminAsignado) {
        return $this->usuarioModel->actualizarAdminAsignado($idUsuario, $adminAsignado);
    }
    
    public function cambiarEstado($id, $estado) {
        return $this->usuarioModel->cambiarEstado($id, $estado);
    }

    public function aprobarUsuario($id) {
        return $this->usuarioModel->cambiarEstado($id, 'Activo');
    }

    public function desvincularUsuario($id) {
        return $this->usuarioModel->desvincularUsuario($id);
    }

    public function logout() {
        $_SESSION = array();
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
        }
        session_destroy();
    }
}

class UsuarioController {
    private $authService;

    public function __construct($authService) {
        $this->authService = $authService;
    }

    public function manejarPeticion() {
        $requestMethod = filter_input(INPUT_SERVER, 'REQUEST_METHOD') ?: ($_SERVER['REQUEST_METHOD'] ?? '');

        if ($requestMethod === 'POST') {
            $accion = $_POST['accion'] ?? '';
            if ($accion === 'registrar') {
                $this->registrar();
            } elseif ($accion === 'asignar_admin') {
                $this->asignarAdmin();
            } else {
                $this->login();
            }
        } elseif ($requestMethod === 'GET') {
            if (isset($_GET['logout'])) {
                $this->logout();
            } elseif (isset($_GET['accion'])) {
                $accion = $_GET['accion'];
                if ($accion === 'listar') {
                    $this->listar();
                } elseif ($accion === 'aprobar' && isset($_GET['id'])) {
                    $this->aprobar($_GET['id']);
                } elseif ($accion === 'eliminar' && isset($_GET['id'])) {
                    $this->desvincular($_GET['id']);
                }
            }
        }
    }

    private function login() {
        $correo = trim($_POST['correo'] ?? $_POST['email'] ?? '');
        $contraseña = $_POST['contraseña'] ?? $_POST['password'] ?? '';
        
        $usuario = $this->authService->login($correo, $contraseña);

        if ($usuario) {
            $_SESSION['usuario_id'] = $usuario['id'] ?? null;
            $_SESSION['usuario_nombre'] = ($usuario['nombre'] ?? '') . " " . ($usuario['apellido'] ?? '');
            $_SESSION['usuario_rol'] = $usuario['rol'] ?? 'Operario';
            $_SESSION['estado'] = $usuario['estado'] ?? 'Pendiente'; 
            
            if ($_SESSION['usuario_rol'] === 'Administrador') {
                $_SESSION['admin_id'] = $usuario['id']; 
                header("Location: ../views/dashboard.php");
            } else {
                $_SESSION['admin_id'] = $usuario['admin_asignado'] ?? null; 
                header("Location: ../views/dashboard_empleado.php");
            }
            exit();
        } else {
            header("Location: ../views/login.php?error=1");
            exit();
        }
    }

    private function registrar() {
        $rol = $_POST['rol'] ?? 'Operario';
        
        $datos = array(
            'nombre' => $_POST['nombre'] ?? '',
            'apellido' => $_POST['apellido'] ?? '',
            'correo' => $_POST['correo'] ?? $_POST['email'] ?? '',
            'contraseña' => $_POST['contraseña'] ?? $_POST['password'] ?? '',
            'rol' => $rol,
            'empresa' => $_POST['empresa'] ?? null,
            'estado' => ($rol === 'Administrador') ? 'Activo' : 'Pendiente'
        );
        
        $resultado = $this->authService->registrar($datos);
        
        if ($resultado) {
            header("Location: ../views/login.php?registro=exitoso");
        } else {
            header("Location: ../views/registro.php?error=1");
        }
        exit();
    }

    private function listar() {
        require_once __DIR__ . '/../views/usuarios.php';
    }

    private function asignarAdmin() {
        if (session_status() == PHP_SESSION_NONE) { session_start(); }
        $idUsuario = $_SESSION['usuario_id'] ?? null;
        $adminAsignado = $_POST['admin_asignado'] ?? null;

        if ($idUsuario && $adminAsignado) {
            $this->authService->actualizarAdminAsignado($idUsuario, $adminAsignado);
            $this->authService->cambiarEstado($idUsuario, 'Pendiente');
            
            $_SESSION['admin_id'] = $adminAsignado; 
            $_SESSION['estado'] = 'Pendiente'; 
        }
        header("Location: ../views/usuarios.php");
        exit();
    }
    
    private function aprobar($id) {
        $this->authService->aprobarUsuario($id);
        header("Location: ../views/usuarios.php");
        exit();
    }

    private function desvincular($id) {
        $this->authService->desvincularUsuario($id);
        header("Location: ../views/usuarios.php");
        exit();
    }

    private function logout() {
        $this->authService->logout();
        header("Location: ../index.php"); 
        exit();
    }
}

$authService = new AuthService(new UsuarioModel());
$controller = new UsuarioController($authService);
$controller->manejarPeticion();
?>