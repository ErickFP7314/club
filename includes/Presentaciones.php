<?php
class Presentaciones {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Obtener todas las presentaciones públicas
    public function getPresentacionesPublicas() {
        $sql = "SELECT p.*, CONCAT(u.nombre, ' ', u.apellido) as autor 
                FROM presentaciones p 
                LEFT JOIN usuarios u ON p.usuario_id = u.id 
                WHERE p.es_publica = 1 
                ORDER BY p.fecha_subida DESC";
        $result = $this->conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Obtener presentaciones por usuario
    public function getPresentacionesPorUsuario($usuario_id) {
        $stmt = $this->conn->prepare("
            SELECT p.*, CONCAT(u.nombre, ' ', u.apellido) as autor 
            FROM presentaciones p 
            LEFT JOIN usuarios u ON p.usuario_id = u.id 
            WHERE p.usuario_id = ? 
            ORDER BY p.fecha_subida DESC
        ");
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Subir una nueva presentación
    public function subirPresentacion($titulo, $descripcion, $url, $imagen_portada, $usuario_id, $es_publica = 1) {
        $stmt = $this->conn->prepare("
            INSERT INTO presentaciones (titulo, descripcion, url, imagen_portada, usuario_id, es_publica) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("ssssii", $titulo, $descripcion, $url, $imagen_portada, $usuario_id, $es_publica);
        
        if($stmt->execute()) {
            return $this->conn->insert_id;
        }
        return false;
    }

    // Actualizar una presentación existente
    public function actualizarPresentacion($id, $titulo, $descripcion, $url, $imagen_portada = null, $es_publica = 1) {
        $sql = "UPDATE presentaciones SET titulo = ?, descripcion = ?, url = ?, es_publica = ?";
        $params = array($titulo, $descripcion, $url, $es_publica);
        $types = "ssii";
        
        if($imagen_portada) {
            $sql .= ", imagen_portada = ?";
            $params[] = $imagen_portada;
            $types .= "s";
        }
        
        $sql .= " WHERE id = ?";
        $params[] = $id;
        $types .= "i";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        return $stmt->execute();
    }

    // Eliminar una presentación
    public function eliminarPresentacion($id, $usuario_id = null) {
        $sql = "DELETE FROM presentaciones WHERE id = ?";
        $params = array($id);
        $types = "i";
        
        if($usuario_id) {
            $sql .= " AND usuario_id = ?";
            $params[] = $usuario_id;
            $types .= "i";
        }
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        return $stmt->execute();
    }

    // Obtener una presentación por ID
    public function getPresentacionPorId($id) {
        $stmt = $this->conn->prepare("
            SELECT p.*, CONCAT(u.nombre, ' ', u.apellido) as autor 
            FROM presentaciones p 
            LEFT JOIN usuarios u ON p.usuario_id = u.id 
            WHERE p.id = ?
        ");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
}
?>
