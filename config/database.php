<?php
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', 'Erick@123'); // Contraseña actualizada
define('DB_NAME', 'club_ciencias');

class Database {
    private $conn;
    
    public function __construct() {
        $this->conn = null;
        
        try {
            $this->conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
            
            // Verificar la conexión
            if ($this->conn->connect_error) {
                throw new Exception("Error de conexión: " . $this->conn->connect_error);
            }
            
            // Establecer el conjunto de caracteres a utf8
            $this->conn->set_charset("utf8");
            
        } catch (Exception $e) {
            die("ERROR: " . $e->getMessage());
        }
    }
    
    // Obtener la conexión
    public function getConnection() {
        return $this->conn;
    }
}

// Crear instancia de la base de datos
$database = new Database();
$conn = $database->getConnection();
?>
