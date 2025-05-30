<?php
require_once 'database.php';

// Crear la base de datos si no existe
$sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
if ($conn->query($sql) === TRUE) {
    echo "Base de datos creada exitosamente o ya existía<br>";
} else {
    die("Error al crear la base de datos: " . $conn->error);
}

// Seleccionar la base de datos
$conn->select_db(DB_NAME);

// Crear tabla de usuarios
$sql = "CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    apellido VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    rol ENUM('admin', 'miembro', 'visitante') DEFAULT 'visitante',
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ultimo_login DATETIME,
    activo BOOLEAN DEFAULT 1
)";

if ($conn->query($sql) === TRUE) {
    echo "Tabla de usuarios creada exitosamente<br>";
} else {
    die("Error al crear la tabla de usuarios: " . $conn->error);
}

// Crear tabla de insignias
$sql = "CREATE TABLE IF NOT EXISTS insignias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    descripcion TEXT,
    imagen VARCHAR(255),
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Tabla de insignias creada exitosamente<br>";
} else {
    die("Error al crear la tabla de insignias: " . $conn->error);
}

// Crear tabla de relación usuario_insignias
$sql = "CREATE TABLE IF NOT EXISTS usuario_insignias (
    usuario_id INT,
    insignia_id INT,
    fecha_obtencion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (usuario_id, insignia_id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (insignia_id) REFERENCES insignias(id) ON DELETE CASCADE
)";

if ($conn->query($sql) === TRUE) {
    echo "Tabla de relación usuario_insignias creada exitosamente<br>";
} else {
    die("Error al crear la tabla usuario_insignias: " . $conn->error);
}

// Crear tabla de presentaciones
$sql = "CREATE TABLE IF NOT EXISTS presentaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    descripcion TEXT,
    url VARCHAR(255) NOT NULL,
    imagen_portada VARCHAR(255),
    usuario_id INT,
    fecha_subida TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    es_publica BOOLEAN DEFAULT 1,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
)";

if ($conn->query($sql) === TRUE) {
    echo "Tabla de presentaciones creada exitosamente<br>";
} else {
    die("Error al crear la tabla de presentaciones: " . $conn->error);
}

// Insertar insignias por defecto si no existen
$insignias = [
    ['Asistencia Perfecta', 'Otorgada por asistencia impecable a todas las reuniones', 'insignias/asistencia_perfecta.png'],
    ['Sabio del Sábado', 'Por participación destacada en las actividades del sábado', 'insignias/sabio_sabado.png'],
    ['Super Jugador', 'Por destacar en actividades lúdicas y juegos', 'insignias/super_jugador.png'],
    ['Café con Ideas', 'Por contribuir con ideas innovadoras durante las reuniones', 'insignias/cafe_ideas.png']
];

foreach ($insignias as $insignia) {
    $stmt = $conn->prepare("INSERT IGNORE INTO insignias (nombre, descripcion, imagen) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $insignia[0], $insignia[1], $insignia[2]);
    $stmt->execute();
}

echo "Base de datos inicializada correctamente con las tablas y datos iniciales.";

$conn->close();
?>
