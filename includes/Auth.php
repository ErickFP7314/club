<?php
session_start();

class Auth {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Registrar un nuevo usuario
    public function registrar($nombre, $apellido, $email, $password, $rol = 'visitante') {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare("INSERT INTO usuarios (nombre, apellido, email, password, rol) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $nombre, $apellido, $email, $hashed_password, $rol);
        
        if($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }

    // Iniciar sesión
    public function login($email, $password) {
        $stmt = $this->conn->prepare("SELECT id, nombre, apellido, password, rol FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if(password_verify($password, $user['password'])) {
                // Actualizar último inicio de sesión
                $update = $this->conn->prepare("UPDATE usuarios SET ultimo_login = NOW() WHERE id = ?");
                $update->bind_param("i", $user['id']);
                $update->execute();
                
                // Establecer variables de sesión
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['nombre'] = $user['nombre'];
                $_SESSION['apellido'] = $user['apellido'];
                $_SESSION['email'] = $email;
                $_SESSION['rol'] = $user['rol'];
                $_SESSION['loggedin'] = true;
                
                return true;
            }
        }
        return false;
    }

    // Cerrar sesión
    public function logout() {
        $_SESSION = array();
        session_destroy();
        return true;
    }

    // Verificar si el usuario está autenticado
    public function isLoggedIn() {
        return isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
    }

    // Obtener información del usuario actual
    public function getCurrentUser() {
        if($this->isLoggedIn()) {
            return [
                'id' => $_SESSION['user_id'],
                'nombre' => $_SESSION['nombre'],
                'apellido' => $_SESSION['apellido'],
                'email' => $_SESSION['email'],
                'rol' => $_SESSION['rol']
            ];
        }
        return null;
    }

    // Verificar si el usuario tiene un rol específico
    public function hasRole($role) {
        return $this->isLoggedIn() && $_SESSION['rol'] === $role;
    }

    // Obtener todas las insignias de un usuario
    public function getInsigniasUsuario($usuario_id) {
        $stmt = $this->conn->prepare("
            SELECT i.*, ui.fecha_obtencion 
            FROM insignias i
            JOIN usuario_insignias ui ON i.id = ui.insignia_id
            WHERE ui.usuario_id = ?
            ORDER BY ui.fecha_obtencion DESC
        ");
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Agregar una insignia a un usuario
    public function agregarInsignia($usuario_id, $insignia_id) {
        $stmt = $this->conn->prepare("INSERT IGNORE INTO usuario_insignias (usuario_id, insignia_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $usuario_id, $insignia_id);
        return $stmt->execute();
    }
}
?>
